(function (wp) {

    var el = wp.element.createElement;
    var components = wp.components;
    var blockControls = wp.editor.BlockControls;
    var inspectorControls = wp.editor.InspectorControls;
    var allCharts = wpdatacharts.data;

    var charts = [];

    if (allCharts !== null){
        for (var i = 0; i < allCharts.length; i++) {
            charts.push({
                value: allCharts[i].id,
                text: allCharts[i].name + ' (id: ' + allCharts[i].id + ')'
            })
        }
    } else {
        charts = []
    }

    // Registering the Block for catalog shortcode
    wp.blocks.registerBlockType('wpdatatables/wpdatacharts-gutenberg-block', {
        title: wpdatacharts.title,
        description: wpdatacharts.description,
        icon: el('svg', {width: '45', height: '50', viewBox: '0 0 45 50'},
            el('rect', {
                style: {fill: '#0089FF', x:'0', y:'0', width:'9', height:'50'},
                transform: 'translate(3.000000, 0.000000)',

                }),
            el('rect', {
                style: {fill: '#56D2FF', x:'12', y:'11', width:'9', height:'39'},
                transform: 'translate(3.000000, 0.000000)',

            }),
            el('rect', {
                style: {fill: '#00A3FF', x:'24', y:'21', width:'9', height:'29'},
                transform: 'translate(3.000000, 0.000000)',
            }),
            el('rect', {
                style: {fill: '#061972', x:'36', y:'29', width:'9', height:'21'},
                transform: 'translate(3.000000, 0.000000)',
            })
        ),
        category: 'wpdatatables-blocks',
        keywords: [
            'wpdatacharts',
            'charts'
        ],
        supports: {
            customClassName: false,
            html: false
        },
        attributes: {
            short_code: {
                type: 'string',
                default: ''
            },
            chartID: {
                type: 'string',
                default: ''
            }
        },
        edit: function (props) {
            var inspectorElements = [];
            var attributes = props.attributes;
            var options = [];

            options['charts'] = [];

            function getOptions(data) {
                var options = [];
                data = Object.keys(data).map(function (key) {
                    return data[key]
                });

                data.sort(function (a, b) {
                    if (parseInt(a.value) < parseInt(b.value))
                        return -1;
                    if (parseInt(a.value) > parseInt(b.value))
                        return 1;
                    return 0
                });

                data.forEach(function (element) {
                    options.push({value: element.value, label: element.text})
                });

                return options;
            }

            getOptions(charts)
                .forEach(function (element) {
                    options['charts'].push(element);
                });

            function getShortCode(props, attributes) {
                var short_code = '';

                if (attributes.chartID !== '') {
                    short_code += '[wpdatachart id=' + attributes.chartID  + ']'
                } else if (charts.length !== 0) {
                    short_code = '[wpdatachart id=' + charts[0].value + ']'
                } else {
                    short_code += 'Please create wpdatachart first.';
                }

                props.setAttributes({short_code: short_code});

                return short_code
            }
            if (charts.length !== 0) {
                inspectorElements.push(el(components.SelectControl, {
                    id: 'wpdatatables-js-select-chart',
                    label: 'Select wpdatachart:',
                    value: attributes.chartID,
                    options: options.charts,
                    onChange: function (selectControl) {
                        return props.setAttributes({chartID: selectControl})
                    }
                }));
            } else {
                inspectorElements.push(el('p', {style: {'margin-bottom': '1em'}}, 'Please create wpdatachart first. You can check how to do that on link below.'));
                inspectorElements.push(el('a', {href:'https://wpdatatables.com/documentation/wpdatacharts/creating-charts-wordpress-wpdatachart-wizard/', target:'_blank', style: {'margin-bottom': '1em'}}, 'How to create chart in wpdatatables?'));
            }

            return [
                el(blockControls, {key: 'controls'}),
                el(inspectorControls, {key: 'inspector'},
                    el(components.PanelBody, {initialOpen: true},
                        inspectorElements
                    )
                ),
                el('div', {},
                    getShortCode(props, props.attributes)
                )
            ]
        },

        save: function (props) {
            return (
                el('div', {},
                    props.attributes.short_code
                )
            )
        }
    })
})(
    window.wp
);