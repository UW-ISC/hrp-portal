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
        icon: el('svg', {width: '58', height: '47', viewBox: '0 0 58 47'},
            el('path', {
                style: {fill: '#008CFF'},
                d: 'M0.179929715,11.7269973 L0.179929715,20.2720523 C0.179929715,21.9656668 1.56795894,23.3513514 3.26443911,23.3513514 L11.8239527,23.3513514 L11.8239527,31.8964063 C11.8239527,33.5900208 13.2119819,34.9757054 14.9084621,34.9757054 L23.4679757,34.9757054 L23.4679757,11.7269973 L0.179929715,11.7269973 Z'
            }),
            el('path', {
                style: {fill: '#5DD3FF'},
                transform: 'translate(11.823953, 0.000000)',
                d: 'M41.8979193,0.102643303 L4.72958108,0.102643303 C2.13345233,0.102643303 0.025704245,2.20683101 0.025704245,4.7985744 L0.025704245,11.7269973 L11.6697272,11.7269973 L23.3137502,23.3513514 L23.3137502,11.7269973 L34.9577732,11.7269973 L34.9577732,23.3513514 L23.3137502,23.3513514 L45.2394712,45.2400356 C46.0877113,44.3932284 46.6275004,43.2128304 46.6275004,41.9041283 L46.6275004,4.7985744 C46.6017962,2.20683101 44.4940481,0.102643303 41.8979193,0.102643303 Z'
            }),
            el('path', {
                style: {fill: '#5DD3FF'},
                transform: 'translate(11.823953, 0.000000)',
                d: 'M11.6697272,41.9041283 C11.6697272,44.4958717 13.7774753,46.6000594 16.3736041,46.6000594 L23.3137502,46.6000594 L11.6697272,34.9757054 L11.6697272,41.9041283 Z'
            }),
            el('polygon', {
                style: {fill: '#00A5FF'},
                points: '0.10281698 11.8039798 11.74684 11.8039798 0.10281698 0.17962578',
                transform: 'translate(23.390863, 11.547372)'
            }),
            el('path', {
                style: {fill: '#00A5FF'},
                transform: 'translate(23.390863, 11.547372)',
                d: 'M11.74684,11.8039798 L11.74684,23.4283338 L0.10281698,23.4283338 L11.74684,35.0526879 L30.3310091,35.0526879 C31.6419256,35.0526879 32.7986166,34.5138105 33.6725609,33.6670033 L11.74684,11.8039798 Z'
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
        example: () => {},
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