<template>
  <v-container>
    <v-card flat>
      <v-card-title>
        <v-avatar color="grey lighten-5">
          <v-img :src="require('../assets/logo.png')" />
        </v-avatar>
        <v-card-title> Flip - 人力資源管理系統 </v-card-title>
        <v-progress-circular indeterminate color="red" />
      </v-card-title>
    </v-card>
    <v-card outlined rounded>
      <v-card-title>
        Flip 是一個專注於為臺灣中小企業所設計的人力資源管理系統。
      </v-card-title>
      <v-card-subtitle> 快速、簡單、明瞭 </v-card-subtitle>
    </v-card>
  </v-container>
</template>

<script>
export default {
  name: "Loading",
};
</script>
