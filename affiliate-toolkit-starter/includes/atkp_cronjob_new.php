<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_cronjob_new {
	/**
	 * Construct the cron
	 */
	public function __construct( $echo_messages ) {

		$this->echo_messages = $echo_messages;

	}

	private function collect_queue() {
		/** @var atkp_queue_entry[] $list */

		$list = array();

		$list = apply_filters( 'atkp_queue_collect_entries', $list );

		return $list;
	}

	private function clean_queues() {
		$deleted = atkp_queue::clean_queues();

		$this->send_message( "deleted queues: " . implode( ',', $deleted ) );
	}

	/**
	 * Prueft, ob eine wieder aufgenommene Queue noch Fortschritt macht, und bricht sie
	 * andernfalls ab. Als Vergleichswert dient die Anzahl der offenen Eintraege beim letzten
	 * Lauf, die in internalstatus hinterlegt wird. retries zaehlt dabei die Laeufe ohne
	 * Fortschritt und wird bei Fortschritt zurueckgesetzt.
	 *
	 * @param atkp_queue $atkp_queue Die aktive Queue
	 * @param float $diff_lastactivity Minuten seit dem letzten geschriebenen Eintrag
	 *
	 * @return bool true wenn die Queue weiter verarbeitet werden soll, false bei Abbruch
	 */
	private function handle_stalled_queue( $atkp_queue, $diff_lastactivity ) {

		$remaining = $atkp_queue->get_prepared_count();

		$this->send_message( 'open entries: ' . $remaining );

		//nothing left to do - the finalization below sets the final status, so this is not a stall
		if ( $remaining == 0 ) {
			return true;
		}

		$baseline = $atkp_queue->internalstatus;

		if ( $baseline === null || $baseline === '' || intval( $baseline ) !== $remaining ) {
			//first resume or progress since the last run
			$atkp_queue->internalstatus = $remaining;
			$atkp_queue->retries        = 0;
			$atkp_queue->save();

			return true;
		}

		/**
		 * Minutes without a single written entry before a queue is considered stalled. The
		 * wall clock guard is needed in addition to the retry counter below, otherwise a
		 * frequently running cronjob would abort a queue whose provider is only slow.
		 *
		 * @param int $minutes
		 */
		$minminutes = intval( apply_filters( 'atkp_queue_stall_minutes', 30 ) );

		if ( $diff_lastactivity < $minminutes ) {
			$this->send_message( 'queue made no progress, but last activity is only ' . $diff_lastactivity . ' minutes old' );

			return true;
		}

		/**
		 * Number of consecutive cron runs without any progress before a queue is aborted.
		 *
		 * @param int $maxretries
		 */
		$maxretries = intval( apply_filters( 'atkp_queue_max_stall_retries', 3 ) );

		if ( $atkp_queue->retries < $maxretries ) {
			$this->send_message( 'queue made no progress (' . $atkp_queue->retries . '/' . $maxretries . ')' );

			return true;
		}

		/* translators: %1$s: number of runs, %2$s: minutes since last activity, %3$s: number of open entries */
		$message = sprintf( __( 'Queue aborted: no progress in %1$s runs and no activity for %2$s minutes, %3$s entries were not processed', 'affiliate-toolkit-starter' ), $atkp_queue->retries, $diff_lastactivity, $remaining );

		$affected = $atkp_queue->abort( $message );

		$this->send_message( 'queue aborted, entries marked as error: ' . $affected );

		try {
			do_action( 'atkp_queue_aborted', $atkp_queue->id );
		} catch ( Throwable $ex ) {
			$this->send_message( $ex->getMessage() );
		}

		return false;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This is a cron job, nonce verification is not applicable.
	public function do_work( $iswpcronjob = false, $mode = '' ) {

		try {
			define( 'ATKP_CRONJOB', true );
			$this->send_message( '### cronjob started ###' );

			$max_execution = intval( ini_get( "max_execution_time" ) );
			$time_start    = microtime( true );

			ATKPTools::set_setting( 'atkp_cron_last_start', time() );

			$this->send_message( 'Max Execution Time: ' . $max_execution );

			if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES == 1 ) {
				$this->send_message( '"SAVEQUERIES" defined: ' . SAVEQUERIES );
				$this->send_message( 'The SAVEQUERIES definition saves the database queries to an array and that array can be displayed to help analyze those queries. This will have a performance impact on your site, so make sure to turn this off when you are not debugging.' );
			} else {
				$this->send_message( '"SAVEQUERIES" not defined' );
			}

			//runs on every cronjob, not only after a queue has completed - otherwise a single
			//stalled queue stops the cleanup permanently and the entries table keeps growing
			$this->send_message( 'clean queues' );
			$this->clean_queues();
			$this->send_message( 'clean queues finished' );

			$atkp_queue = null;

			if ( atkp_queue::exists_notfinished() ) {
				$override = ATKPTools::get_get_parameter( 'override_lastactivity', 'string' );

				$this->send_message( 'queue already exists' );

				//if last activity older then 5 minutes -> run queue
				$atkp_queue = atkp_queue::get_active_queue();

				$lastactivity = $atkp_queue->get_last_activity();
				$this->send_message( 'last activity: ' . $atkp_queue->updatedon );
				$this->send_message( 'last activity entries: ' . $lastactivity );

				//a queue without any entry has no activity timestamp - treat it as long inactive so
				//the run below can finalize it instead of blocking the pipeline forever
				$diff_lastactivity = $lastactivity == null ? 99999 : round( abs( time() - strtotime( $lastactivity ) ) / 60, 2 );

				if ( ! $override ) {
					if ( $diff_lastactivity <= 5 ) {
						$this->send_message( 'queue is still running...' );

						return;
					}
				}

				//the queue is resumed - check whether it made any progress since the last run.
				//without this check a queue that always dies on the same package (fatal error,
				//timeout, out of memory) stays 'active' forever, blocks every new queue and
				//stops the cleanup of old queues, because clean_queues only ran after a queue
				//had completed.
				if ( ! $this->handle_stalled_queue( $atkp_queue, $diff_lastactivity ) ) {
					return;
				}

			} else {
				//if nothing open, create a new package

				$list = $this->collect_queue();

				if ( count( $list ) > 0 ) {
					$this->send_message( 'new queue will be created...' );

					$grouped = array();
					$type    = '';
					foreach ( $list as $entry ) {
						$grouped[ $entry->post_type ] = $entry->post_type;
					}

					if ( array_key_exists( 'atkp_shop', $grouped ) ) {
						$newlist = array();

						foreach ( $list as $entry ) {
							if ( $entry->post_type == 'atkp_shop' ) {
								$newlist[] = $entry;
							}
						}
						$list                 = $newlist;
						$grouped              = array();
						$grouped['atkp_shop'] = 'atkp_shop';
					}

					foreach ( $grouped as $xx => $xx2 ) {
						if ( $type == '' ) {
							$type = $xx;
						} else {
							$type .= ', ' . $xx;
						}
					}

					ATKPTools::create_queue( $type, '', $iswpcronjob, $list, atkp_queue_status::ACTIVE );

					$atkp_queue = atkp_queue::get_active_queue();
				} else {
					$this->send_message( 'no new queue created' );
				}
			}

			$this->do_basic_work();

			if ( $atkp_queue != null ) {
				$override = ATKPTools::get_get_parameter( 'override_timeframe', 'string' );

				if ( $override != 'yes' ) {
					$from = atkp_options::$loader->get_cron_from();
					$to   = atkp_options::$loader->get_cron_to();

					if ( $from != '' && $to != '' && ( $from != '00:00' && $to != '00:00' ) ) {
						$begin = strtotime( $from );
						$end   = strtotime( $to );
						$now   = time();

						if ( $begin > $end ) {
							$end = $end + 86400;
						}

						if ( ! ( $now >= $begin && $now <= $end ) ) {
							$this->send_message( 'time ' . gmdate( 'd.m.y H:i:s', $now ) . ' is NOT between ' . gmdate( 'd.m.y H:i:s', $begin ) . ' and ' . gmdate( 'd.m.y H:i:s', $end ) . ' - queue will not be processed' );

							return;
						}
					}
				}

				$this->send_message( 'queue will be processed...' );

				$atkp_queue->retries = $atkp_queue->retries + 1;
				$atkp_queue->save();

				//process the queue

				//get entries (grouped shopid) 10 pieces and run the update

				ATKPTools::set_setting( 'atkp_cron_last_processed', time() );

				while ( true ) {

					/** @var atkp_queue_entry[] $entries */

					if ( ! $iswpcronjob && ! class_exists( 'WP_CLI' ) && $max_execution > 0 ) {
						set_time_limit( $max_execution ); // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged -- Required for long-running cron operations.
					}

					$entries = $atkp_queue->get_next_entries( atkp_queue_entry_status::PREPARED );

					$this->send_message( 'entries to process: ' . count( $entries ) );

					if ( count( $entries ) == 0 ) {
						break;
					} else {
						$functionname = $entries[0]->functionname;
						$shopid       = $entries[0]->shop_id;

						if ( $shopid != '' && $shopid != '0' ) {
							$status = get_post_status( $shopid );

							if ( ! ( $status == 'draft' || $status == 'publish' ) ) {
								foreach ( $entries as $entry ) {
									$entry->status         = atkp_queue_entry_status::ERROR;
									$entry->updatedmessage = __( 'Shop status invalid: ', 'affiliate-toolkit-starter' ) . $status;

									$entry->save();
								}
								continue;
							}
						}

						$this->send_message( 'atkp_queue_process_entries_' . $functionname . ' before call' );
						//$this->send_message('$entries: '. serialize($entries));
						//$this->send_message('$shopid: '. serialize($shopid));
						//$this->send_message('attached filters: '. ATKPTools::get_attached_filters('atkp_queue_process_entries_' . $functionname, true));

						try {
							$entries_bak = $entries;
							$entries     = apply_filters( 'atkp_queue_process_entries_' . $functionname, $entries, $shopid );

							if ( $entries == null || count( $entries ) == 0 ) {
								$entries = $entries_bak;
								$this->send_message( 'atkp_queue_process_entries_' . $functionname . ' did not returned $entries' );
							} else {
								$this->send_message( 'atkp_queue_process_entries_' . $functionname . ' returned $entries: ' . count( $entries ) );
							}

							$saved_ids = array();

							foreach ( $entries as $entry ) {
								if ( $entry->status == atkp_queue_entry_status::PREPARED ) {
									$entry->status         = atkp_queue_entry_status::NOT_PROCESSED;
									$entry->updatedmessage = __( 'Entry was not updated via function', 'affiliate-toolkit-starter' );
								}

								$entry->save();

								$saved_ids[ $entry->id ] = true;
							}

							//entries the hook dropped from its return value would stay 'prepared'
							//and get_next_entries would deliver the same batch again endlessly
							foreach ( $entries_bak as $entry ) {
								if ( isset( $saved_ids[ $entry->id ] ) ) {
									continue;
								}

								$entry->status         = atkp_queue_entry_status::NOT_PROCESSED;
								$entry->updatedmessage = __( 'Entry was not updated via function', 'affiliate-toolkit-starter' );

								$entry->save();
							}
						} catch ( Throwable $e ) {

							$this->send_message( 'atkp_queue_process_entries_' . $functionname . ' exception: ' . $e->getMessage() );

							foreach ( $entries as $entry ) {
								$entry->status         = atkp_queue_entry_status::ERROR;
								/* translators: %s: error message from exception */
							$entry->updatedmessage = sprintf( __( 'Exception in entries hook: %s', 'affiliate-toolkit-starter' ), $e->getMessage() );

								$entry->save();
							}
						}
					}
				}

				//TODO: Collect errors from entries and define status
				if ( $atkp_queue->has_errors() ) {
					$atkp_queue->status = atkp_queue_status::ERROR;
				} else {
					$atkp_queue->status = atkp_queue_status::SUCCESSFULLY;
				}
				$atkp_queue->save();

				try {
					do_action( 'atkp_queue_finished', $atkp_queue->id );
				} catch ( Throwable $ex ) {
					$this->send_message( $ex->getMessage() );
				}
			}

			if ( atkp_options::$loader->get_check_enabled() ) {
				$lastdatacheck = atkp_options::$loader->get_cron_lastdatacheck();
				$run_check     = true;
				if ( $lastdatacheck != '' ) {
					$diff_lastdatacheck = round( abs( time() - $lastdatacheck ) / 60, 2 );

					//wenn keine fortsetzung, dann prüfen
					if ( $diff_lastdatacheck <= atkp_options::$loader->get_notification_interval() ) {
						$this->send_message( 'next data check (hours): ' . round( ( atkp_options::$loader->get_notification_interval() - $diff_lastdatacheck ) / 60, 2 ) );
						$run_check = false;
					}
				}

				if ( $run_check ) {
					try {
						do_action( 'atkp_datacheck_report' );
					} catch ( Throwable $e ) {
						ATKPLog::LogError( $e->getMessage() );
					}

					update_option( ATKP_PLUGIN_PREFIX . '_cron_lastdatacheck', time() );
				}
			}

			$this->send_message( 'Total Execution Time: ' . ( microtime( true ) - $time_start ) . ' Seconds' );
			$this->send_message( '### cronjob finished ###' );
		} catch ( Throwable $e ) {
			$this->send_message( '### cronjob error ###' );
			$this->send_message( $e->getMessage() );
		}

		ATKPTools::set_setting( 'atkp_cron_last_processed', time() );

		if ( ! $iswpcronjob ) {
			echo esc_html( 'OK' );
			exit;
		}
	}

	function do_basic_work() {
		ATKP_StoreController::get_product_discounts();

		ATKP_LicenseController::check_license_status();
	}

	public function send_message( $message ) {
		if ( class_exists( 'WP_CLI' ) ) {
			WP_CLI::log( $message );
		} else {
			if ( $this->echo_messages ) {
				echo( esc_html( $message ) . '<br />' . PHP_EOL );
			}
		}

		if ( ATKPLog::$logenabled ) {
			ATKPLog::LogDebug( $message );
		}
	}


}


?>
