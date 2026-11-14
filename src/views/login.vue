<template>
  <main class="login-page">
    <form class="login-card" @submit.prevent="mode === 'login' ? submitLogin() : submitRegister()">
      <p class="eyebrow">PROJECT HUB</p>
      <h1>{{ mode === 'login' ? 'เข้าสู่ระบบ' : 'สมัครพนักงาน' }}</h1>
      <p class="intro">
        {{ mode === 'login' ? 'กรอกชื่อผู้ใช้และรหัสผ่านเพื่อดำเนินการต่อ' : 'กรอกข้อมูลเพื่อสร้างบัญชีพนักงาน' }}
      </p>

      <template v-if="mode === 'register'">
        <label for="firstName">ชื่อ</label>
        <input id="firstName" v-model.trim="registration.first_name" type="text" autocomplete="given-name" maxlength="100" required>

        <label for="lastName">นามสกุล</label>
        <input id="lastName" v-model.trim="registration.last_name" type="text" autocomplete="family-name" maxlength="100" required>

        <label for="email">อีเมล</label>
        <input id="email" v-model.trim="registration.email" type="email" autocomplete="email" maxlength="190" required>
      </template>

      <label for="username">ชื่อผู้ใช้</label>
      <input id="username" v-model.trim="username" type="text" autocomplete="username" maxlength="80" required>

      <label for="password">รหัสผ่าน</label>
      <input
        id="password"
        v-model="password"
        type="password"
        :autocomplete="mode === 'login' ? 'current-password' : 'new-password'"
        :minlength="mode === 'register' ? 8 : undefined"
        required
      >

      <p v-if="message" class="message" role="alert">{{ message }}</p>
      <button type="submit" :disabled="loading">
        {{ loading ? 'กำลังดำเนินการ…' : (mode === 'login' ? 'เข้าสู่ระบบ' : 'สมัครพนักงาน') }}
      </button>
      <button class="mode-toggle" type="button" :disabled="loading" @click="toggleMode">
        {{ mode === 'login' ? 'ยังไม่มีบัญชี? สมัครพนักงาน' : 'มีบัญชีแล้ว? เข้าสู่ระบบ' }}
      </button>
      <router-link class="home-link" to="/">กลับหน้าหลัก</router-link>
    </form>
  </main>
</template>

<script>
export default {
  name: 'LoginView',
  data() {
    return {
      username: '',
      password: '',
      loading: false,
      message: '',
      mode: 'login',
      registration: {
        first_name: '',
        last_name: '',
        email: ''
      }
    }
  },
  methods: {
    toggleMode() {
      this.mode = this.mode === 'login' ? 'register' : 'login'
      this.message = ''
    },
    async submitLogin() {
      this.loading = true
      this.message = ''

      try {
        const response = await fetch('/projecthub/php_api/login.php', {
          method: 'POST',
          credentials: 'include',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ username: this.username, password: this.password })
        })
        const result = await response.json()

        if (!response.ok || !result.success) {
          this.message = result.message || 'เข้าสู่ระบบไม่สำเร็จ'
          return
        }

        window.alert('เข้าสู่ระบบได้แล้ว')
        this.$router.push('/')
      } catch (error) {
        this.message = 'เชื่อมต่อระบบไม่ได้ ตรวจสอบว่า Apache และฐานข้อมูลกำลังทำงาน'
      } finally {
        this.loading = false
      }
    },
    async submitRegister() {
      this.loading = true
      this.message = ''

      try {
        const response = await fetch('/projecthub/php_api/register.php', {
          method: 'POST',
          credentials: 'include',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            ...this.registration,
            username: this.username,
            password: this.password
          })
        })
        const result = await response.json()

        if (!response.ok || !result.success) {
          this.message = result.message || 'สมัครสมาชิกไม่สำเร็จ'
          return
        }

        this.mode = 'login'
        this.password = ''
        this.message = `${result.message} รหัสพนักงาน: ${result.employee_code} กรุณาเข้าสู่ระบบ`
      } catch (error) {
        this.message = 'เชื่อมต่อระบบไม่ได้ ตรวจสอบว่า Apache และฐานข้อมูลกำลังทำงาน'
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style scoped>
.login-page {
  min-height: calc(100vh - 100px);
  display: grid;
  place-items: center;
  padding: 32px 20px;
  background: #f4f7fb;
}

.login-card {
  display: grid;
  gap: 12px;
  width: min(100%, 400px);
  padding: 36px;
  border: 1px solid #e4e9f1;
  border-radius: 18px;
  background: #fff;
  box-shadow: 0 16px 40px rgb(27 45 75 / 8%);
  text-align: left;
}

.eyebrow {
  margin: 0;
  color: #43856c;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.14em;
}

h1 {
  margin: 0;
  color: #1c2b3a;
  font-size: 28px;
}

.intro {
  margin: 0 0 12px;
  color: #657487;
}

label {
  color: #344253;
  font-size: 14px;
  font-weight: 600;
}

input {
  width: 100%;
  box-sizing: border-box;
  padding: 12px 14px;
  border: 1px solid #ccd5e0;
  border-radius: 9px;
  font: inherit;
}

input:focus {
  border-color: #43856c;
  outline: 3px solid rgb(67 133 108 / 16%);
}

button {
  margin-top: 8px;
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

.mode-toggle {
  margin-top: 0;
  padding: 8px;
  background: transparent;
  color: #28775b;
  font-size: 14px;
}

.mode-toggle:hover:not(:disabled) {
  background: #f0f7f3;
}

.message {
  margin: 0;
  color: #b42318;
  font-size: 14px;
}

.home-link {
  margin-top: 4px;
  color: #526579;
  text-align: center;
  text-decoration: none;
}

.home-link:hover {
  text-decoration: underline;
}
</style>
