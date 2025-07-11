<?php

/** @var $formatter atkp_formatter */
/** @var $parameters atkp_template_parameters */
/** @var $products atkp_product[]|null */
?>
<div class="atkp-detailoffers_nologo-box {{$parameters->get_css_container_class()}}">
    @if($products != null)
        @foreach ($products as $product)
            @foreach($formatter->get_offers($product, true) as $offer)
                <div class="atkp-pricecompare-mobile-title">
                    <a {!! $formatter->get_offer_productlink($offer) !!} >{{ ATKPTools::substrwords( $offer->product != null ? $offer->product->title : $product->title, 80)}}</a>
                </div>
                <div class="atkp-pricecompare-container atkp-pricecompare-nologo  {{$parameters->get_css_element_class()}}">
                    <div class="atkp-pricecompare-row">
                        <div class="atkp-pricecompare-cell atkp-pricecompare-logo">
                            {!! $offer->shop->get_logourl() != '' ? '<img src="'.$offer->shop->get_logourl().'"  alt="'.esc_attr($formatter->get_shop_title($offer->shop)).'" />' : '' !!}
                        </div>
                        <div class="atkp-pricecompare-cell atkp-pricecompare-title">
                            <a {!! $formatter->get_offer_productlink($offer) !!} >{{ ATKPTools::substrwords( $offer->product != null ? $offer->product->title : $product->title, 100)}}</a>
                        </div>

                        <div class="atkp-pricecompare-cell atkp-pricecompare-price">
                            <div class="atkp-price ">{{$formatter->get_offer_price($offer, '%s')}} <span
                                        class="atkp-shipping">{{$formatter->get_offer_shipping($offer, ' +%s', '')}}</span>
                            </div>
                            <div class="atkp-oldprice"
                                 style="text-decoration: line-through;">{{$formatter->get_offer_oldprice($offer, '%s')}}</div>
                            <div class="atkp-stock">{{$formatter->get_offer_availability($offer, '%s')}}</div>
                        </div>
                        <div class="atkp-pricecompare-cell atkp-pricecompare-button">
                            <a {!! $formatter->get_offer_productlink($offer) !!} class="atkp-button">{!! $formatter->get_offer_linktext() !!}{!! $formatter->get_mark() !!}</a>
                            <span class="atkp-shopname"> {!! $offer->shop->get_title() !!}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        @endforeach

            @if(is_array($products) && count($products) > 0 && $parameters->get_show_disclaimer())
            <span class="atkp-disclaimer">{!! $formatter->get_disclaimer($products[0], $parameters->get_disclaimer_text()) !!}</span>
        @endif
    @endif

</div>