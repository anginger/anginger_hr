<template>
  <v-card>
    <v-container class="py-8 px-6" fluid>
      <v-row v-if="chart_data.length" class="justify-center">
        <v-card light>
          <v-card-text>
            <doughnut :chartdata="chart_collection" :options="chart_options" />
          </v-card-text>
        </v-card>
      </v-row>
      <v-row>
        <v-col v-for="(card, name) in cards" :key="name" cols="12">
          <v-card>
            <v-subheader>{{ name }}</v-subheader>
            <item-list :empty="card.length < 1" :loaded="card.loaded">
              <template #data>
                <v-list-item v-for="(item, index) in card.data" :key="index">
                  <v-list-item-content>
                    <v-list-item-title>
                      {{ item.title }}
                    </v-list-item-title>
                    <v-list-item-subtitle>
                      {{ item.description }}
                    </v-list-item-subtitle>
                  </v-list-item-content>
                </v-list-item>
              </template>
            </item-list>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </v-card>
</template>

<script>
import ItemList from "@/components/ItemList";
import Doughnut from "@/components/Chart/Doughnut";
import { randomColors } from "@/components/Chart/Utils";

export default {
  name: "Overview",
  components: { ItemList, Doughnut },
  data: () => ({
    chart_data: [],
    chart_options: {
      title: {
        display: true,
        text: "人數總表",
      },
    },
    cards: {
      Employee: {
        loaded: false,
        data: [],
      },
      Department: {
        loaded: false,
        data: [],
      },
    },
  }),
  created() {
    this.load();
  },
  methods: {
    async load() {
      const { data } = await this.$axios.get("/manage/");
      this.cards.Employee.data = [
        {
          title: "員工數量",
          description: `總共有 ${data.employees.count} 個員工`,
        },
        {
          title: "員工薪資",
          description: `平均薪資為 ${data.employees.salaries_avg} 元`,
        },
        {
          title: "員工縣市分佈",
          description: `員工來自於 ${data.employees.city_count} 個縣市`,
        },
      ];
      this.cards.Department.data = [
        {
          title: "部門數量",
          description: `總共有 ${data.departments.count} 個部門`,
        },
        {
          title: "部門薪資",
          description: `平均薪資為 ${data.departments.salaries_avg} 元`,
        },
        {
          title: "部門人數平均",
          description: `平均每個部門有 ${data.departments.employees_count_avg} 個員工`,
        },
      ];
      this.chart_data = data.departments_employees_counts;
      this.cards.Employee.loaded = true;
      this.cards.Department.loaded = true;
    },
  },
  computed: {
    chart_collection() {
      return {
        datasets: [
          {
            data: this.chart_data.map((i) => i[1]),
            backgroundColor: Object.keys(this.chart_data).map(randomColors),
            hoverOffset: 4,
          },
        ],
        labels: this.chart_data.map((i) => i[0]),
      };
    },
  },
};
</script>
