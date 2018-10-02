<template>
    <div id="chartContainer">
        <button-switcher :options="[
                    {text: 'Today', value: 'today'},
                    {text: '7 days', value: '7days'},
                    {text: '30 days', value: '30days'},
                    {text: 'Since publishing', value: 'all'}]"
                         v-model="interval">
        </button-switcher>

        <div v-if="loading" class="preloader pls-purple">
            <svg class="pl-circular" viewBox="25 25 50 50">
                <circle class="plc-path" cx="50" cy="50" r="20"></circle>
            </svg>
        </div>

        <div id="events-legend-wrapper">
            <div id="events-legend"
                 v-if="eventLegend.data"
                 v-show="eventLegend.visible"
                 v-bind:style="{ left: eventLegend.left }">
                Popis eventu
            </div>
        </div>

        <div ref="svg-container" style="height: 200px" id="article-chart">
            <svg style="z-index: 10" ref="svg"></svg>
        </div>

        <div id="legend-wrapper">
            <div v-if="legend.data" v-show="legend.visible" v-bind:style="{ left: legend.left }" id="article-graph-legend">

                <span>{{legend.data.startDate | formatDate(data.intervalMinutes)}}</span>
                <table>
                    <tr>
                        <th>Source</th>
                        <th>Value</th>
                    </tr>
                    <tr v-for="item in legend.data.values">
                        <td>
                            <span style="font-weight: bold" v-bind:style="{color: item.color}">&#9679;</span>&nbsp;
                            <span v-if="item.tag==''">Uncategorized</span>
                            <span v-else style="text-transform: capitalize">{{item.tag}}</span>
                        </td>
                        <td>{{item.value}}</td>
                    </tr>
                    <tr style="border-top: 1px solid #d1d1d1">
                        <td><b>Total</b></td>
                        <td><b>{{legend.data.sum}}</b></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</template>

<style>
</style>

<style scoped>
    #chartContainer {
        position: relative;
    }

    #legend-wrapper, #events-legend-wrapper {
        position: relative;
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
        z-index: 1000;
        top:0;
        left: 0;
        opacity: 0.85;
        color: #fff;
        padding: 2px;
        background-color: #494949;
        border-radius: 2px;
        border: 2px solid #494949;
        transform: translate(-50%, 0px)
    }

    #events-legend {
        position:absolute;
        z-index: 1000;
        bottom:-20px;
        left: 0;
        opacity: 0.85;
        color: #fff;
        padding: 2px;
        background-color: #00bdf1;
        border-radius: 2px;
        border: 2px solid #00bdf1;
        transform: translate(-50%, 0px)
    }

    #chartContainer .preloader {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%)
    }
</style>

<script>
    import ButtonSwitcher from './ButtonSwitcher.vue'
    import * as constants from './constants'
    import {debounce, formatInterval} from './constants'
    import axios from 'axios'
    import * as d3 from 'd3'

    const props = {
        url: {
            type: String,
            required: true
        }
    }

    Vue.filter('formatDate', formatInterval)

    function stackMax(layer) {
        return d3.max(layer, function (d) { return d[1]; });
    }

    const bisectDate = d3.bisector(d => d.date).left;

    let container, svg, dataG, eventsG, x,y, colorScale, xAxis, vertical, mouseRect,
        margin = {top: 20, right: 20, bottom: 20, left: 20},
        loadDataTimer = null

    export default {
        components: {
            ButtonSwitcher
        },
        name: 'article-chart',
        props: props,
        data() {
            return {
                data: null,
                loading: false,
                interval: 'today',
                legend: {
                    visible: false,
                    data: null,
                    left: "0px"
                },
                eventLegend: {
                    visible: false,
                    data: null,
                    left: "0px"
                }
            };
        },
        watch: {
            data(val) {
                this.fillData()
            },
            interval(value) {
                clearInterval(loadDataTimer)
                this.loadData()
                loadDataTimer = setInterval(this.loadData, constants.REFRESH_DATA_TIMEOUT_MS)
            }
        },
        mounted() {
            this.createGraph()
            this.loadData()
            loadDataTimer = setInterval(this.loadData, constants.REFRESH_DATA_TIMEOUT_MS)
            window.addEventListener('resize', debounce((e) => {
                this.fillData()
            }, 100));
        },
        methods: {
            createGraph(){
                container = this.$refs["svg-container"]
                let outerWidth = container.clientWidth,
                    outerHeight = container.clientHeight,
                    width = outerWidth - margin.left - margin.right,
                    height = outerHeight - margin.top - margin.bottom

                svg = d3.select(this.$refs["svg"])
                    .attr("width", outerWidth)
                    .attr("height", outerHeight)
                    .append("g")
                    .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

                dataG = svg.append("g").attr("class", "data-g")
                x = d3.scaleTime().range([0, width])
                y = d3.scaleLinear().range([height, 0])

                xAxis = d3.axisBottom(x).ticks(5)

                let gX = svg.append("g")
                    .attr("transform", "translate(0," + height + ")")
                    .attr("class", "axis axis--x")
                    .call(xAxis)

                eventsG = svg.append("g").attr("class", "events-g")

                // Mouse events
                let mouseG = svg.append("g")
                    .attr("class", "mouse-over-effects");

                vertical = mouseG.append("path")
                    .attr("class", "mouse-line")
                    .style("stroke", "black")
                    .style("stroke-width", "1px")
                    .style("opacity", "0");

                let that = this
                // append a rect to catch mouse movements on canvas
                mouseRect = mouseG.append('svg:rect')
                    .attr('width', width)
                    .attr('height', height)
                    .attr('fill', 'none')
                    .attr('pointer-events', 'all')
                    .on('mouseout', function() {
                        that.legend.visible = false
                        vertical
                            .style("opacity", "0");
                    })
                    .on('mouseover', function() {
                        that.legend.visible = true
                        vertical
                            .style("opacity", "1");
                    })
                    .on('mousemove', function() {
                        if (that.data !== null) {
                            let mouse = d3.mouse(this);
                            const xDate = x.invert(mouse[0])
                            that.highlightRow(xDate, height)
                            that.highlightEvent(xDate)
                        }
                    })
            },
            highlightEvent(xDate) {
                const xDateMillis = moment(xDate).valueOf()
                const pxThresholdToShowLegend = 50

                // get closest event (not using bisect, as there are only few events to search)
                let selectedEvent, min = Number.MAX_SAFE_INTEGER
                for (const event of this.data.events) {
                    let diff = Math.abs(xDateMillis - moment(event.date).valueOf())
                    if (diff < min) {
                        min = diff
                        selectedEvent = event
                    }
                }

                // show event only if its too close to x-position of mouse
                if (Math.abs(x(selectedEvent.date) - x(xDate)) < pxThresholdToShowLegend) {
                    this.eventLegend.left = (x(selectedEvent.date) + margin.left) + "px"
                    this.eventLegend.visible = true
                    this.eventLegend.data = selectedEvent
                } else {
                    this.eventLegend.visible = false
                }
            },
            highlightRow(xDate, height) {
                const xDateMillis = moment(xDate).valueOf()

                function getSelectedRow(rowIndex, data, dateAccessor) {
                    let rowRight = data[rowIndex]
                    let rowLeft = data[rowIndex - 1]
                    let row = rowRight

                    // Find out which value is closer
                    if (rowLeft !== undefined) {
                        const leftDateMillis = moment(dateAccessor(rowLeft)).valueOf()
                        const rightDateMillis = moment(dateAccessor(rowRight)).valueOf()
                        if ((xDateMillis - leftDateMillis) < (rightDateMillis - xDateMillis)){
                            row = rowLeft
                        }
                    }
                    return row
                }

                // get closest row
                let rowIndex = bisectDate(this.data.results, xDate);
                let row = getSelectedRow(rowIndex, this.data.results, (item) => item.date)

                let verticalX = x(row.date)

                vertical
                    .attr("d", function() {
                        let d = "M" + verticalX + "," + height;
                        d += " " + verticalX + "," + 0;
                        return d;
                    })

                let valuesSum = 0

                let values = this.data.tags.map(function (tag) {
                    valuesSum += row[tag]
                    return {
                        tag: tag,
                        value: row[tag],
                        color: d3.color(colorScale(tag)).hex()
                    }
                })

                this.legend.data = {
                    startDate: row.date,
                    values: values,
                    sum: valuesSum
                }

                this.legend.left = (Math.round(x(row.date)) + margin.left) + "px"
            },
            fillData() {
                if (this.data === null){
                    return
                }
                let results = this.data.results,
                    tags = this.data.tags,
                    events = this.data.events

                let outerWidth = container.clientWidth,
                    outerHeight = container.clientHeight,
                    width = outerWidth - margin.left - margin.right,
                    height = outerHeight - margin.top - margin.bottom

                svg.attr("width", outerWidth)
                    .attr("height", outerHeight)

                mouseRect.attr("width", width)
                    .attr("height", height)

                let stack = d3.stack()
                    .keys(tags)
                    .offset(d3.stackOffsetNone)

                let layers = stack(results);

                x.domain([results[0].date, results[results.length - 1].date])
                    .range([0, width])
                y.domain([0, d3.max(layers, stackMax)])
                    .range([height, 0])

                let colors = constants.GRAPH_COLORS

                colorScale = d3.scaleOrdinal()
                    .domain(tags)
                    .range(colors);

                // Remove original data if present
                dataG.selectAll(".layer").remove()
                dataG.selectAll(".layer-line").remove()
                eventsG.selectAll('.event').remove()

                let area = d3.area()
                    .x(function (d, i) { return x(d.data.date) })
                    .y0(function (d) { return y(d[0]); })
                    .y1(function (d) { return y(d[1]); })

                let areaStroke = d3.line()
                    .x((d, i) => x(d.data.date))
                    .y((d) => y(d[1]))

                // Update data
                dataG.selectAll(".layer")
                    .data(layers)
                    .enter().append("g")
                    .attr("class", "layer")
                    .append("path")
                    .attr("d", area)
                    .attr("fill", (d, i) => colors[i])

                dataG.selectAll(".layer-line")
                    .data(layers)
                    .enter().append("g")
                    .attr("class", "layer-line")
                    .append("path")
                    .attr("d", areaStroke)
                    .attr("stroke", "#626262")
                    .attr("opacity", 0.25)
                    .attr("shape-rendering", "geometricPrecision")
                    .attr("fill", "none")

                let eventLayers = eventsG.selectAll(".event")
                    .data(events)
                    .enter().append("g")
                    .attr("class", "event")

                eventLayers
                    .append("path")
                    .attr("d", function(item, i){
                        let xDate = x(item.date)
                        let d = "M" + xDate + "," + height
                        d += " " + xDate + "," + 0
                        return d
                    })
                    .attr("stroke-dasharray", "6 4")
                    .attr("stroke", "#00bdf1")

                eventLayers
                    .append("polygon")
                    .attr("points", function (item, i) {
                        let xDate = x(item.date)
                        // small triangle
                        let size = 5
                        let points = [
                            [xDate - size, 0],
                            [xDate + size, 0],
                            [xDate, size],
                        ]
                        return points.map((p) => p[0] + "," + p[1]).join(" ")
                    })
                    .attr("fill", "#00bdf1")

                // Update axis
                svg.select('.axis--x').transition().call(xAxis)
            },
            loadData() {
                this.loading = true
                axios
                    .get(this.url, {
                        params: {
                            tz: Intl.DateTimeFormat().resolvedOptions().timeZone,
                            interval: this.interval,
                        }
                    })
                    .then(response => {
                        this.loading = false
                        const tags = response.data.tags

                        const addParsedDate = function (d) {
                            let dataObject = {
                                date: d3.isoParse(d.date)
                            }
                            tags.forEach(function (s) {
                                dataObject[s] = +d[s];
                            })
                            return dataObject;
                        }

                        let events = response.data.events.map(function(event) {
                            return {
                                date: d3.isoParse(event.date),
                                event: event
                            }
                        })

                        this.data = {
                            results: response.data.results.map(addParsedDate),
                            events: events,
                            tags: tags,
                            intervalMinutes: response.data.intervalMinutes
                        }
                    })
                    .catch(error => {
                        this.error = error
                        this.loading = false;
                    })
            },
        }
    }
</script>