<template>
    <div :id="`svg-container-${this.chartContainerId}`">
    </div>
</template>

<script>
    import * as d3 from 'd3'

    let props = {
        chartData: {
            type: Array,
            required: true,
        },
        chartContainerId: {
            type: String,
            required: true,
        }
    };

    export default {
        props: props,
        name: 'sparklineChart',
        mounted() {
            this.drawChart(this.chartData);
        },
        methods: {
            drawChart(data) {
                const container = $(`#${this.chartContainerId}`)[0];
                const width = container.clientWidth;
                const height = container.clientHeight;

                const x = d3.scaleTime().range([0, width]);
                const y = d3.scaleLinear().range([height, 0]);
                const parseDate = d3.timeParse("%Y-%m-%dT%H:%M:%SZ");
                const line = d3.line()
                    .curve(d3.curveMonotoneX)
                    .x(function (d) {return x(d.date);})
                    .y(function (d) {return y(d.count);});

                data.forEach(function (d) {
                    d.date = parseDate(d.Date);
                    d.count = d.Count;
                });
                x.domain(d3.extent(data, function(d) { return d.date; }));
                y.domain(d3.extent(data, function(d) { return d.count; }));

                d3.select('#svg-container-' + this.chartContainerId)
                    .append('svg')
                    .attr('width', width)
                    .attr('height', height)
                    .append('path')
                    .datum(data)
                    .attr('d', line)
                    .attr('stroke', '#000')
                    .attr('stroke-width', '0.5px')
                    .attr('fill', 'none');
            }
        }
    }
</script>
