<script>
import { Doughnut } from "vue-chartjs";

export default {
  name: "ChartGraph",
  extends: Doughnut,
  props: {
    chartdata: {
      type: Object,
      required: true,
    },
    options: {
      type: Object,
      required: false,
      default: () => ({}),
    },
  },
  mounted() {
    this.renderChart(this.chartdata, this.options);
  },
};
</script>
