<template>
  <div id="app">
    <header class="site-header">
      <div class="nav-inner">
        <router-link class="brand" to="/" aria-label="HR Connect หน้าหลัก">
          <span class="brand-mark">H</span>
          <span class="brand-copy"><strong>HR Connect</strong><small>PEOPLE OPERATIONS</small></span>
        </router-link>
        <nav aria-label="เมนูหลัก">
          <router-link to="/" class="nav-link"><span aria-hidden="true">⌂</span> หน้าหลัก</router-link>
          <router-link to="/about" class="nav-link"><span aria-hidden="true">▤</span> ประวัติ</router-link>
          <router-link to="/logout" class="nav-link logout-link"><span aria-hidden="true">↗</span> ออกจากระบบ</router-link>
        </nav>
      </div>
    </header>
    <router-view />
  </div>
</template>

<style>
* {
  box-sizing: border-box;
}

body {
  margin: 0;
  background: #f7faf9;
}

#app {
  min-height: 100vh;
  color: #172b3a;
  font-family: Avenir, "Noto Sans Thai", Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

.site-header {
  position: relative;
  z-index: 5;
  border-bottom: 1px solid #e8efec;
  background: rgb(255 255 255 / 92%);
  box-shadow: 0 4px 20px rgb(22 48 39 / 3%);
}

.nav-inner {
  display: flex;
  width: min(100% - 40px, 1200px);
  min-height: 76px;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
  margin: 0 auto;
}

.brand {
  display: inline-flex;
  align-items: center;
  gap: 11px;
  color: inherit;
  text-decoration: none;
}

.brand-mark {
  display: grid;
  width: 42px;
  height: 42px;
  place-items: center;
  border-radius: 14px;
  background: linear-gradient(145deg, #28775b, #44a47c);
  box-shadow: 0 6px 14px rgb(40 119 91 / 22%);
  color: #fff;
  font-size: 21px;
  font-weight: 800;
}

.brand-copy {
  display: grid;
  gap: 2px;
}

.brand-copy strong {
  color: #173a30;
  font-size: 15px;
  letter-spacing: -.02em;
}

.brand-copy small {
  color: #8a9b94;
  font-size: 8px;
  font-weight: 800;
  letter-spacing: .14em;
}

.site-header nav {
  display: flex;
  align-items: center;
  gap: 7px;
}

.nav-link {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 10px 13px;
  border-radius: 10px;
  color: #61736b;
  font-size: 13px;
  font-weight: 700;
  text-decoration: none;
  transition: background .18s ease, color .18s ease;
}

.nav-link:hover,
.nav-link.router-link-exact-active {
  background: #eff7f3;
  color: #28775b;
}

.logout-link {
  margin-left: 5px;
  border: 1px solid #e5ece8;
  background: #fff;
}

@media (max-width: 600px) {
  .nav-inner {
    width: calc(100% - 24px);
    min-height: 68px;
    gap: 8px;
  }

  .brand {
    gap: 7px;
  }

  .brand-mark {
    width: 36px;
    height: 36px;
    border-radius: 11px;
  }

  .brand-copy strong {
    font-size: 12px;
  }

  .brand-copy small {
    font-size: 7px;
  }

  .site-header nav {
    gap: 2px;
  }

  .nav-link {
    gap: 4px;
    padding: 9px 7px;
    font-size: 11px;
  }

  .logout-link {
    margin-left: 0;
  }
}
</style>
