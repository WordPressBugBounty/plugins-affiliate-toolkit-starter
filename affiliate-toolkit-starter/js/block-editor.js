"use strict";
!function () {
    var n = wp.element.Fragment,
        c = wp.editor.BlockControls,
        Button = wp.components.Button,
        e = function t(o) {
            return function (e) {
                var supportedblocks = ATKPSETT.supportedBlocks;

                if (-1 === supportedblocks.indexOf(e.name)) {
                    return React.createElement(o, e);
                }

                // For classic block (Freeform), add a button above the editor
                if (e.name === 'core/freeform') {
                    return React.createElement(n, null,
                        React.createElement('div', {
                            style: {
                                marginBottom: '10px',
                                padding: '8px',
                                backgroundColor: '#f0f0f1',
                                borderRadius: '2px',
                                display: 'flex',
                                alignItems: 'center',
                                gap: '8px'
                            }
                        },
                            React.createElement('img', {
                                src: ATKPSETT.iconurl,
                                alt: 'AT',
                                style: { width: '18px', height: '18px' }
                            }),
                            React.createElement(Button, {
                                isSecondary: true,
                                isSmall: true,
                                onClick: function() {
                                    window.open(ATKPSETT.generatorUrl, '_blank');
                                }
                            }, 'AT Shortcode Generator')
                        ),
                        React.createElement(o, e)
                    );
                }

                // For other supported blocks, use BlockControls
                return React.createElement(n, null,
                    React.createElement(o, e),
                    React.createElement(c, {
                        controls: [{
                            icon: React.createElement('img', {
                                src: ATKPSETT.iconurl,
                                alt: 'AT Shortcode',
                                style: { width: '20px', height: '20px', display: 'block' }
                            }),
                            title: 'AT Shortcode',
                            onClick: function t() {
                                window.open(ATKPSETT.generatorUrl, '_blank');
                            }
                        }]
                    })
                );
            }
        };
    wp.hooks.addFilter("editor.BlockEdit", "shortcodes-atkp/with-insert-shortcode-button", e)
}();
