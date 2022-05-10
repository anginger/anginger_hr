<template>
  <v-container>
    <v-card>
      <v-card-title> 個人檔案 </v-card-title>
      <v-card-subtitle> Profile </v-card-subtitle>
      <v-card-text>
        <ul>
          <li>姓名：{{ profile.display_name }}</li>
          <li>帳號：{{ profile.username }}</li>
          <li>申請日期：{{ profileCreatedAt }}</li>
        </ul>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script>
export default {
  name: "Profile",
  data: () => ({
    profile: {
      display_name: "",
      username: "",
      created_time: 0,
    },
  }),
  computed: {
    profileCreatedAt() {
      return new Date(this.profile.created_time * 1000).toLocaleString();
    },
  },
  methods: {
    load() {
      this.$axios.get("/setting/").then((response) => {
        this.profile = response.data;
      });
    },
  },
  created() {
    this.load();
  },
};
</script>
