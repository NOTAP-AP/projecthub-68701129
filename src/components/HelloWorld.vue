<template>
  <main class="welcome">
    <section class="welcome-card">
      <p class="eyebrow">PROJECT HUB</p>
      <h1>HR Connect</h1>
      <p>{{ msg }}</p>
      <router-link to="/login">เข้าสู่ระบบ</router-link>
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

a:hover {
  background: #1f6048;
}
</style>