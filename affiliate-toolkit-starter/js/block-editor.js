"use strict";
!function () {
    var n = wp.element.Fragment, c = wp.editor.BlockControls, t = wp.components, r = t.SVG, i = t.Path,
        e = function t(o) {
            return function (e) {
                var supportedblocks = ATKPSETT.supportedBlocks;

                return -1 === supportedblocks.indexOf(e.name) ? React.createElement(o, e) : React.createElement(n, null, React.createElement(o, e), React.createElement(c, {
                    controls: [{
                        icon: React.createElement(r, {
                            viewBox: "0 0 35 30",
                            xmlns: "http://www.w3.org/2000/svg"
                        }, React.createElement(i, {d: "M29 4h-26c-1.65 0-3 1.35-3 3v18c0 1.65 1.35 3 3 3h26c1.65 0 3-1.35 3-3v-18c0-1.65-1.35-3-3-3zM3 6h26c0.542 0 1 0.458 1 1v3h-28v-3c0-0.542 0.458-1 1-1zM29 26h-26c-0.542 0-1-0.458-1-1v-9h28v9c0 0.542-0.458 1-1 1zM4 20h2v4h-2zM8 20h2v4h-2zM12 20h2v4h-2z"})),
                        title: 'AT Shortcode',
                        onClick: function t() {
                            var generator_button = jQuery('.atkp-generator-button');
                            generator_button.trigger("click");
                        }
                    }]
                }))
            }
        };
    wp.hooks.addFilter("editor.BlockEdit", "shortcodes-atkp/with-insert-shortcode-button", e)
}();
