<template>
  <main class="welcome">
    <section class="welcome-card">
      <p class="eyebrow">PROJECT HUB</p>
      <h1>HR Connect</h1>
      <p>{{ msg }}</p>
      <div class="login-links">
        <router-link to="/login">เข้าสู่ระบบพนักงาน</router-link>
        <router-link class="admin-link" to="/admin/login">เข้าสู่ระบบผู้ดูแลระบบ</router-link>
      </div>
    </section>
  </main>
</template>

<script>
export default {
  name: 'HelloWorld',
  props: {
    msg: {
      type: String,
      default: 'ระบบบริหารจัดการทรัพยากรบุคคล'
    }
  }
}
</script>

<style scoped>
.welcome {
  min-height: calc(100vh - 100px);
  display: grid;
  place-items: center;
  padding: 32px 20px;
  background: #f4f7fb;
}

.welcome-card {
  width: min(100%, 560px);
  padding: 48px 36px;
  border: 1px solid #e4e9f1;
  border-radius: 20px;
  background: #fff;
  box-shadow: 0 16px 40px rgb(27 45 75 / 8%);
  text-align: center;
}

.eyebrow {
  color: #28775b;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.14em;
}

h1 {
  margin: 12px 0;
  color: #1c2b3a;
  font-size: 36px;
}

.welcome-card > p:not(.eyebrow) {
  color: #657487;
}

a {
  display: inline-block;
  margin-top: 20px;
  padding: 12px 24px;
  border-radius: 9px;
  background: #28775b;
  color: #fff;
  font-weight: 700;
  text-decoration: none;
}

.login-links {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 12px;
}

.login-links a {
  margin-top: 20px;
}

.login-links .admin-link {
  background: #fff;
  color: #28775b;
  box-shadow: inset 0 0 0 1px #28775b;
}

.login-links .admin-link:hover {
  background: #f0f7f3;
}

a:hover {
  background: #1f6048;
}
</style>