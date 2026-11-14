<template>
  <main class="landing-page">
    <section class="hero">
      <div class="hero-copy">
        <p class="eyebrow"><span></span> PEOPLE FIRST · WORK SMARTER</p>
        <h1>ดูแลทุกเรื่อง<br><span>ของคนในทีม</span></h1>
        <p class="hero-description">
          พื้นที่ทำงานที่ช่วยให้เรื่องงานบุคคลเป็นเรื่องง่าย
          จัดการคำขอลาและดูแลทีมได้ในที่เดียว
        </p>
        <div class="hero-actions">
          <router-link class="primary-action" to="/login">เข้าสู่ระบบพนักงาน <span aria-hidden="true">→</span></router-link>
          <router-link class="secondary-action" to="/admin/login">สำหรับผู้ดูแลระบบ</router-link>
        </div>
        <div class="hero-note"><span class="note-check">✓</span> ระบบจัดการงานบุคคลที่ใช้งานง่ายและปลอดภัย</div>
      </div>

      <div class="hero-art" aria-label="ภาพตัวอย่างแดชบอร์ด HR Connect">
        <div class="art-orbit orbit-one"></div>
        <div class="art-orbit orbit-two"></div>
        <div class="dashboard-preview">
          <div class="preview-top">
            <div class="preview-title"><span class="preview-mark">H</span><div><strong>ภาพรวมทีม</strong><small>อัปเดตล่าสุดวันนี้</small></div></div>
            <span class="preview-menu">•••</span>
          </div>
          <div class="welcome-note"><div><small>สวัสดี 👋</small><strong>วันนี้มีอะไรให้จัดการบ้าง?</strong></div><span class="sun-icon">☀</span></div>
          <div class="preview-stats">
            <div class="preview-stat"><span class="stat-icon people-icon">♧</span><small>พนักงาน</small><strong>ทีมของคุณ</strong></div>
            <div class="preview-stat"><span class="stat-icon leave-icon">▤</span><small>คำขอลา</small><strong>จัดการง่าย</strong></div>
          </div>
          <div class="preview-activity">
            <div class="activity-heading"><strong>งานที่เป็นระเบียบ</strong><span>HR CONNECT</span></div>
            <div class="activity-line"><span class="activity-dot green-dot"></span><span class="activity-bar long-bar"></span><span class="activity-tag">พร้อมใช้</span></div>
            <div class="activity-line"><span class="activity-dot gold-dot"></span><span class="activity-bar medium-bar"></span><span class="activity-tag muted-tag">เรียบง่าย</span></div>
          </div>
        </div>
        <div class="floating-badge"><span>✦</span><div><strong>จัดการได้ในที่เดียว</strong><small>ทุกวันทำงานก็ราบรื่น</small></div></div>
      </div>
    </section>

    <section class="features">
      <div class="section-heading"><p class="eyebrow">MADE FOR YOUR TEAM</p><h2>เครื่องมือที่ช่วยให้งานเดินหน้า</h2></div>
      <div class="feature-grid">
        <article class="feature-card">
          <span class="feature-icon people-feature">♧</span>
          <h3>ดูแลข้อมูลทีม</h3>
          <p>จัดการข้อมูลพนักงานให้ค้นหาและดูแลได้สะดวก</p>
        </article>
        <article class="feature-card">
          <span class="feature-icon leave-feature">▤</span>
          <h3>จัดการวันลา</h3>
          <p>ส่งคำขอ ติดตามสถานะ และแนบเอกสารประกอบได้</p>
        </article>
        <article class="feature-card">
          <span class="feature-icon secure-feature">✓</span>
          <h3>ใช้งานอย่างมั่นใจ</h3>
          <p>เข้าใช้งานด้วยบัญชีของคุณ พร้อมการควบคุมสิทธิ์</p>
        </article>
      </div>
    </section>

    <footer class="landing-footer"><span class="footer-mark">H</span><span>HR Connect</span><span class="footer-divider">·</span><span>พื้นที่ทำงานที่ดี เริ่มต้นจากการดูแลกัน</span></footer>
  </main>
</template>

<script>
export default {
  name: 'HomeView'
}
</script>

<style scoped>
.landing-page {
  min-height: calc(100vh - 76px);
  padding: 34px 20px 20px;
  background:
    radial-gradient(ellipse at 82% 12%, rgb(220 242 230 / 58%), transparent 31%),
    #f7faf9;
  color: #172b3a;
}

.hero,
.features,
.landing-footer {
  width: min(100%, 1160px);
  margin: 0 auto;
}

.hero {
  position: relative;
  display: grid;
  min-height: 430px;
  grid-template-columns: 1.03fr .97fr;
  align-items: center;
  gap: 24px;
  overflow: hidden;
  padding: 48px 64px;
  border: 1px solid #e8f0ec;
  border-radius: 28px;
  background: linear-gradient(115deg, #fff 0%, #fbfdfc 49%, #eff8f3 100%);
  box-shadow: 0 20px 55px rgb(32 76 58 / 7%);
}

.hero-copy {
  position: relative;
  z-index: 1;
  max-width: 470px;
}

.eyebrow {
  display: flex;
  align-items: center;
  gap: 9px;
  margin: 0;
  color: #6a8b7b;
  font-size: 10px;
  font-weight: 800;
  letter-spacing: .16em;
}

.eyebrow span {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #48a47a;
  box-shadow: 0 0 0 4px #e4f3ea;
}

h1 {
  margin: 20px 0 13px;
  color: #173a30;
  font-size: clamp(38px, 5vw, 58px);
  font-weight: 800;
  letter-spacing: -.055em;
  line-height: 1.16;
}

h1 span {
  color: #31916a;
}

.hero-description {
  max-width: 390px;
  margin: 0;
  color: #708078;
  font-size: 14px;
  line-height: 1.9;
}

.hero-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 11px;
  margin-top: 25px;
}

.hero-actions a {
  display: inline-flex;
  min-height: 46px;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 0 17px;
  border-radius: 11px;
  font-size: 12px;
  font-weight: 700;
  text-decoration: none;
  transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
}

.hero-actions a:hover {
  transform: translateY(-2px);
}

.primary-action {
  background: #28775b;
  box-shadow: 0 8px 18px rgb(40 119 91 / 20%);
  color: #fff;
}

.primary-action:hover {
  background: #20694f;
  box-shadow: 0 11px 22px rgb(40 119 91 / 27%);
}

.primary-action span {
  font-size: 17px;
}

.secondary-action {
  border: 1px solid #dfeae4;
  background: #fff;
  color: #48665a;
}

.secondary-action:hover {
  background: #f4faf6;
}

.hero-note {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 20px;
  color: #809087;
  font-size: 10px;
}

.note-check {
  display: grid;
  width: 17px;
  height: 17px;
  place-items: center;
  border-radius: 50%;
  background: #e7f5ec;
  color: #32835f;
  font-size: 10px;
  font-weight: 800;
}

.hero-art {
  position: relative;
  display: grid;
  min-height: 330px;
  place-items: center;
}

.art-orbit {
  position: absolute;
  border: 1px solid rgb(57 146 103 / 12%);
  border-radius: 50%;
}

.orbit-one {
  width: 370px;
  height: 370px;
}

.orbit-two {
  width: 290px;
  height: 290px;
}

.dashboard-preview {
  position: relative;
  z-index: 1;
  width: min(100%, 350px);
  padding: 18px;
  border: 1px solid #e8efeb;
  border-radius: 19px;
  background: rgb(255 255 255 / 94%);
  box-shadow: 0 22px 50px rgb(31 75 56 / 13%);
  transform: rotate(2deg);
}

.preview-top,
.preview-title,
.welcome-note,
.preview-stats,
.activity-heading,
.activity-line {
  display: flex;
  align-items: center;
}

.preview-top,
.welcome-note,
.activity-heading,
.activity-line {
  justify-content: space-between;
}

.preview-title {
  gap: 9px;
}

.preview-mark,
.footer-mark {
  display: grid;
  width: 31px;
  height: 31px;
  place-items: center;
  border-radius: 10px;
  background: #e8f5ed;
  color: #2f8d63;
  font-weight: 800;
}

.preview-title div,
.welcome-note div {
  display: grid;
  gap: 3px;
}

.preview-title strong {
  color: #284337;
  font-size: 11px;
}

.preview-title small,
.welcome-note small {
  color: #95a49b;
  font-size: 8px;
}

.preview-menu {
  color: #9baaa1;
  letter-spacing: 2px;
}

.welcome-note {
  margin-top: 16px;
  padding: 14px;
  border-radius: 12px;
  background: linear-gradient(110deg, #eff8f2, #f8fbf8);
}

.welcome-note small {
  color: #789084;
}

.welcome-note strong {
  color: #305b46;
  font-size: 11px;
}

.sun-icon {
  display: grid;
  width: 31px;
  height: 31px;
  place-items: center;
  border-radius: 10px;
  background: #fff4d7;
  color: #d89624;
}

.preview-stats {
  gap: 9px;
  margin-top: 10px;
}

.preview-stat {
  display: grid;
  flex: 1;
  gap: 5px;
  padding: 11px;
  border: 1px solid #eef2ef;
  border-radius: 11px;
}

.stat-icon {
  display: grid;
  width: 25px;
  height: 25px;
  place-items: center;
  border-radius: 8px;
  font-size: 13px;
}

.people-icon {
  background: #eaf5ee;
  color: #38835f;
}

.leave-icon {
  background: #fff5df;
  color: #bd8a2c;
}

.preview-stat small {
  color: #94a198;
  font-size: 8px;
}

.preview-stat strong {
  color: #354c40;
  font-size: 10px;
}

.preview-activity {
  margin-top: 12px;
  padding: 12px;
  border-radius: 11px;
  background: #fbfcfb;
}

.activity-heading strong {
  color: #4a6054;
  font-size: 9px;
}

.activity-heading span {
  color: #a4b1a9;
  font-size: 7px;
  font-weight: 800;
  letter-spacing: .1em;
}

.activity-line {
  gap: 7px;
  margin-top: 10px;
}

.activity-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
}

.green-dot { background: #4ca77a; }
.gold-dot { background: #e0b14f; }

.activity-bar {
  height: 5px;
  margin-right: auto;
  border-radius: 8px;
  background: #e8efeb;
}

.long-bar { width: 42%; }
.medium-bar { width: 30%; }

.activity-tag {
  padding: 3px 6px;
  border-radius: 5px;
  background: #e8f5ed;
  color: #43835f;
  font-size: 7px;
}

.muted-tag {
  background: #f3f4f2;
  color: #89968e;
}

.floating-badge {
  position: absolute;
  z-index: 2;
  right: -2px;
  bottom: 24px;
  display: flex;
  align-items: center;
  gap: 9px;
  padding: 10px 13px;
  border: 1px solid #e7efea;
  border-radius: 12px;
  background: #fff;
  box-shadow: 0 10px 25px rgb(31 75 56 / 10%);
  transform: rotate(-2deg);
}

.floating-badge > span {
  display: grid;
  width: 27px;
  height: 27px;
  place-items: center;
  border-radius: 8px;
  background: #eff8f2;
  color: #348960;
}

.floating-badge div {
  display: grid;
  gap: 3px;
}

.floating-badge strong {
  color: #3a5547;
  font-size: 9px;
}

.floating-badge small {
  color: #97a49c;
  font-size: 8px;
}

.features {
  padding: 38px 4px 22px;
}

.section-heading {
  margin-bottom: 17px;
  text-align: center;
}

.section-heading .eyebrow {
  justify-content: center;
}

.section-heading h2 {
  margin: 7px 0 0;
  color: #274638;
  font-size: clamp(19px, 2.5vw, 24px);
  letter-spacing: -.03em;
}

.feature-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 13px;
}

.feature-card {
  padding: 17px 19px;
  border: 1px solid #e8efeb;
  border-radius: 14px;
  background: #fff;
  box-shadow: 0 4px 15px rgb(27 60 43 / 3%);
}

.feature-icon {
  display: grid;
  width: 34px;
  height: 34px;
  place-items: center;
  border-radius: 10px;
  font-size: 16px;
}

.people-feature { background: #eaf5ee; color: #38835f; }
.leave-feature { background: #fff5df; color: #bd8a2c; }
.secure-feature { background: #edf2ff; color: #5b71bd; }

.feature-card h3 {
  margin: 12px 0 5px;
  color: #314c3e;
  font-size: 12px;
}

.feature-card p {
  margin: 0;
  color: #87958d;
  font-size: 10px;
  line-height: 1.7;
}

.landing-footer {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 0 5px;
  color: #91a098;
  font-size: 9px;
}

.footer-mark {
  width: 20px;
  height: 20px;
  border-radius: 7px;
  font-size: 10px;
}

.footer-divider {
  color: #bdc9c2;
}

@media (max-width: 900px) {
  .hero {
    grid-template-columns: 1fr .9fr;
    padding: 38px;
  }
}

@media (max-width: 700px) {
  .landing-page {
    padding: 18px 12px;
  }

  .hero {
    grid-template-columns: 1fr;
    gap: 8px;
    padding: 34px 24px 24px;
  }

  .hero-copy {
    max-width: 100%;
  }

  .hero-art {
    min-height: 295px;
  }

  .floating-badge {
    right: 0;
    bottom: 7px;
  }

  .features {
    padding-top: 28px;
  }

  .feature-grid {
    grid-template-columns: 1fr;
    gap: 9px;
  }

  .feature-card {
    display: grid;
    grid-template-columns: 38px 1fr;
    column-gap: 11px;
    align-items: center;
    padding: 12px;
  }

  .feature-card h3 {
    margin: 0;
  }

  .feature-card p {
    grid-column: 2;
  }
}

@media (max-width: 420px) {
  h1 {
    font-size: 39px;
  }

  .hero {
    padding-right: 17px;
    padding-left: 17px;
  }

  .hero-actions {
    align-items: stretch;
    flex-direction: column;
  }

  .hero-actions a {
    justify-content: center;
  }

  .landing-footer {
    flex-wrap: wrap;
    text-align: center;
  }
}
</style>
