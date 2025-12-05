<canvas id="myChart2" style="width:100%;height:300px "></canvas>
<script src="{{asset('backend/js/Chart.min.js')}}"></script>
<script>
var chartData = {
    labels: @json($chartData->pluck('date')),
    datasets: [
        {
            fillColor: "#79D1CF",
            strokeColor: "#79D1CF",
            data: @json($chartData->pluck('total'))
        }
    ]
};



var ctx = document.getElementById("myChart2").getContext("2d");
var myBar = new Chart(ctx).Bar(chartData, {
    showTooltips: false,
    onAnimationComplete: function () {

        var ctx = this.chart.ctx;
        ctx.font = this.scale.font;
        ctx.fillStyle = this.scale.textColor;
        ctx.textAlign = "center";
        ctx.textBaseline = "bottom";

        this.datasets.forEach(function (dataset) {
            dataset.bars.forEach(function (bar) {
                ctx.fillText(bar.value, bar.x, bar.y - 0);
            });
        })
    }
});
</script>
