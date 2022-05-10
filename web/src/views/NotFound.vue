<template>
  <v-container>
    <v-card flat>
      <v-card-title> 頁面不存在 </v-card-title>
      <v-card-subtitle>
        您所請求的頁面不存在，請檢查您的網址，再試一次
      </v-card-subtitle>
      <v-card-actions>
        <v-btn @click="back" depressed>返回</v-btn>
        <v-spacer />
        <v-btn @click="home" color="primary" depressed>首頁</v-btn>
      </v-card-actions>
    </v-card>
  </v-container>
</template>

<script>
export default {
  name: "NotFound",
  methods: {
    back() {
      if (this.$router.history.length) {
        this.$router.back();
      } else {
        this.$router.replace("/");
      }
    },
    home() {
      if (this.$route.path !== "/") {
        this.$router.replace("/");
      }
    },
  },
};
</script>
