<template>
  <v-container>
    <v-card flat>
      <v-card-title>
        <v-avatar color="grey lighten-5">
          <v-img :src="require('../assets/logo.png')" />
        </v-avatar>
        <v-card-title> Flip - 人力資源管理系統 </v-card-title>
      </v-card-title>
    </v-card>
    <v-card outlined rounded>
      <v-card-title>發生錯誤</v-card-title>
      <v-card-subtitle>
        Flip
        與後端伺服器通訊失敗，伺服器可能尚未設定、網路失效，或遇到未知錯誤，請稍後再試。
      </v-card-subtitle>
    </v-card>
  </v-container>
</template>

<script>
export default {
  name: "Disconnected",
};
</script>
