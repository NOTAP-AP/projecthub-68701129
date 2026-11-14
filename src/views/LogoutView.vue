<template>
  <main class="logout-page">
    <section class="logout-card">
      <p class="eyebrow">PROJECT HUB</p>
      <h1>ออกจากระบบ</h1>
      <p>ต้องการออกจากระบบบัญชีนี้หรือไม่?</p>
      <p v-if="error" class="error-message" role="alert">{{ error }}</p>
      <button type="button" :disabled="loading" @click="logout">
        {{ loading ? 'กำลังออกจากระบบ…' : 'ยืนยันออกจากระบบ' }}
      </button>
      <router-link to="/about">ยกเลิก</router-link>
    </section>
  </main>
</template>

<script>
export default {
  name: 'LogoutView',
  data() {
    return {
      loading: false,
      error: ''
    }
  },
  methods: {
    async logout() {
      this.loading = true
      this.error = ''

      try {
        const response = await fetch('/projecthub/php_api/logout.php', {
          method: 'POST',
          credentials: 'include'
        })
        const result = await response.json()

        if (!response.ok || !result.success) {
          this.error = result.message || 'ออกจากระบบไม่สำเร็จ กรุณาลองอีกครั้ง'
          return
        }

        this.$router.replace('/login')
      } catch (error) {
        this.error = 'เชื่อมต่อระบบไม่ได้ กรุณาลองอีกครั้ง'
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style scoped>
.logout-page {
  min-height: calc(100vh - 100px);
  display: grid;
  place-items: center;
  padding: 32px 20px;
  background: #f4f7fb;
}

.logout-card {
  display: grid;
  gap: 16px;
  width: min(100%, 400px);
  padding: 36px;
  border: 1px solid #e4e9f1;
  border-radius: 18px;
  background: #fff;
  box-shadow: 0 16px 40px rgb(27 45 75 / 8%);
}

.eyebrow {
  margin: 0;
  color: #43856c;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.14em;
}

h1,
p {
  margin: 0;
}

h1 {
  color: #1c2b3a;
  font-size: 28px;
}

.error-message {
  color: #b42318;
}

button {
  padding: 13px;
  border: 0;
  border-radius: 9px;
  background: #28775b;
  color: #fff;
  cursor: pointer;
  font: inherit;
  font-weight: 700;
}

button:disabled {
  cursor: wait;
  opacity: 0.7;
}

a {
  color: #526579;
  text-align: center;
  text-decoration: none;
}

a:hover {
  text-decoration: underline;
}
</style>
