<template>
    <div>
        <select id="colorSelect">
            <option value=input>Color by input</option>
            <option value=output>Color by output</option>
            <option value=path selected>Color by input-output</option>
        </select>
        <div id='container'>
            <svg id='chart' xmlns="http://www.w3.org/2000/svg" ></svg>
        </div>
    </div>
</template>

<style scoped>
    #article-chart {
        height: 200px;
        position: relative;

    }

    #article-chart .mouse-catch-block {
        position: absolute;
        display: block;
        bottom: -10px;
        height: 10px;
        width: 100%;
        background-color: transparent;
    }

    .settings-box {
        display: flex;
        align-items: center;
        justify-content: right;
        padding-right: 30px;
        padding-left: 30px;
    }

    .external-events-wrapper {
        display:inline-block;
        width: 220px;
    }

    #chartContainer {
        position: relative;
    }

    #chartContainerHeader {
        padding: 20px 30px 0px 30px;
    }

    #legend-wrapper {
        position: relative;
        overflow: visible;
        height:0;
    }
    #article-graph-legend table {
        width: 100%;
        background-color: transparent;
        border-collapse: collapse;
    }
    #article-graph-legend table td, th {
        padding: 3px 6px
    }
    #article-graph-legend {
        position:absolute;
        white-space:nowrap;
        z-index: 1000;
        top:0;
        left: 0;
        opacity: 0.95;
        color: #fff;
        padding: 2px;
        background-color: #494949;
        border-radius: 2px;
        border: 2px solid #494949;
        transform: translate(-50%, 0px)
    }

    .legend-title {
        text-align: center;
        font-size: 14px;
    }

    #chartContainer .preloader {
        z-index: 2000;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%)
    }

    .events-legend-wrapper {
        position: relative;
        height: 0;
    }

    /* div to correctly wrap transformed .events-legend */
    .events-legend-wrapper > div {
        position: absolute;
        z-index: 1000;
        bottom:-21px;
        width: auto;
    }

    .events-legend {
        max-width: 220px;
        opacity: 0.85;
        color: #fff;
        padding: 2px;
        margin-top: 2px;
        background-color: #00bdf1;
        border-radius: 2px;
        border: 2px solid #00bdf1;
        transform: translate(-50%, 0px)
    }
</style>

<script>
    import axios from 'axios'
    import * as d3Base from 'd3'
    import { sankey, sankeyLinkHorizontal } from 'd3-sankey'

    const d3 = Object.assign(d3Base, { sankey, sankeyLinkHorizontal });

    let props = {
        dataUrl: {
            type: String,
            required: true
        },
        conversionSourceType: {
            type: String,
            required: true
        },
        nodeColors: {
            type: Array,
            required: true
        }
    };

    export default {
        name: 'conversions-sankey-diagram',
        props: props,
        created() {
            console.log('we are in sankey component');
        },
        mounted() {
            console.log('we are in mounted part of component');
            this.loadData();
        },
        methods: {
            loadData() {
                axios
                    .get(this.dataUrl, {
                        tz: Intl.DateTimeFormat().resolvedOptions().timeZone,
                        // interval: this.interval,
                        conversionSourceType: this.conversionSourceType,
                    })
                    .then(response => {
                        console.log(response.data);
                        this.createDiagram(response.data);
                    })
            },
            createDiagram(data) {
                const width = 964;
                const height = 600;

                let edgeColor = 'path';

                const _sankey = d3.sankey()
                    .nodeWidth(15)
                    .nodePadding(10)
                    .extent([[1, 1], [width - 1, height - 5]])
                    .nodeId(function (d) {
                        return d.name;
                    });
                const sankey = ({nodes, links}) => _sankey({
                    nodes: nodes.map(d => Object.assign({}, d)),
                    links: links.map(d => Object.assign({}, d))
                });


                const f = d3.format(",.0f");
                const format = d => `${f(d)}`;

                const color = name => typeof this.nodeColors[name] !== 'undefined' ? this.nodeColors[name] : 'grey';

                const svg = d3.select('#chart')
                    .attr("viewBox", `0 0 ${width} ${height}`)
                    .style("width", "100%")
                    .style("height", "auto");

                const {nodes, links} = sankey(data);

                svg.append("g")
                    // .attr("stroke", "#000")
                    .selectAll("rect")
                    .data(nodes)
                    .enter()
                    .append("rect")
                    .attr("x", d => d.x0)
                    .attr("y", d => d.y0)
                    .attr("height", d => d.y1 - d.y0)
                    .attr("width", d => d.x1 - d.x0)
                    .attr("fill", d => color(d.name))
                    .append("title")
                    .text(d => `${d.name}\n${format(d.value)}`);

                const link = svg.append("g")
                    .attr("fill", "none")
                    .attr("stroke-opacity", 0.5)
                    .selectAll("g")
                    .data(links)
                    .enter()
                    .append("g")
                    .style("mix-blend-mode", "multiply");

                const select = document.querySelector('#colorSelect');
                select.onchange = () => {
                    edgeColor = select.value;
                    update();
                };

                function update() {
                    if (edgeColor === "path") {
                        const gradient = link.append("linearGradient")
                            .attr("id", (d,i) => {
                                //  (d.uid = DOM.uid("link")).id
                                const id = `link-${i}`;
                                d.uid = `url(#${id})`;
                                return id;
                            })
                            .attr("gradientUnits", "userSpaceOnUse")
                            .attr("x1", d => d.source.x1)
                            .attr("x2", d => d.target.x0);

                        gradient.append("stop")
                            .attr("offset", "0%")
                            .attr("stop-color", d => color(d.source.name));

                        gradient.append("stop")
                            .attr("offset", "100%")
                            .attr("stop-color", d => color(d.target.name));
                    }

                    link.append("path")
                        .attr("d", d3.sankeyLinkHorizontal())
                        .attr("stroke", d => edgeColor === "path" ? d.uid
                            : edgeColor === "input" ? color(d.source.name)
                                : color(d.target.name))
                        .attr("stroke-width", d => Math.max(1, d.width));
                }

                update();

                link.append("title")
                    .text(d => `${d.source.name} → ${d.target.name}\n${format(d.value)}`);

                svg.append("g")
                    .style("font", "10px sans-serif")
                    .selectAll("text")
                    .data(nodes)
                    .enter()
                    .append("text")
                    .attr("x", d => d.x0 < width / 2 ? d.x1 + 6 : d.x0 - 6)
                    .attr("y", d => (d.y1 + d.y0) / 2)
                    .attr("dy", "0.35em")
                    .attr("text-anchor", d => d.x0 < width / 2 ? "start" : "end")
                    .text(d => d.name);
            }
        }
    }
</script>
