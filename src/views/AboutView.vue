<template>
  <main class="leave-page">
    <div class="page-container">
      <header class="page-heading">
        <div>
          <p class="eyebrow">HR CONNECT</p>
          <h1>จัดการการลางาน</h1>
          <p>ตรวจสอบคำขอลาและส่งคำขอลาใหม่</p>
        </div>
        <button class="primary-button" type="button" @click="openRequestForm">
          <span aria-hidden="true">＋</span> ยื่นใบลาใหม่
        </button>
      </header>

      <div v-if="error" class="alert error-alert" role="alert">
        <span>{{ error }}</span>
        <button v-if="unauthorized" type="button" class="text-button" @click="$router.push('/login')">
          เข้าสู่ระบบ
        </button>
        <button v-else type="button" class="text-button" @click="loadRequests">ลองอีกครั้ง</button>
      </div>
      <p v-if="notice" class="alert success-alert" role="status">{{ notice }}</p>

      <section class="summary-grid" aria-label="สรุปคำขอลา">
        <article class="summary-card">
          <span class="summary-icon total-icon">▤</span>
          <div><p>คำขอทั้งหมด</p><strong>{{ requests.length }}</strong></div>
        </article>
        <article class="summary-card">
          <span class="summary-icon pending-icon">◷</span>
          <div><p>รออนุมัติ</p><strong>{{ countByStatus('Pending') }}</strong></div>
        </article>
        <article class="summary-card">
          <span class="summary-icon approved-icon">✓</span>
          <div><p>อนุมัติแล้ว</p><strong>{{ countByStatus('Approved') }}</strong></div>
        </article>
        <article class="summary-card">
          <span class="summary-icon rejected-icon">×</span>
          <div><p>ไม่อนุมัติ</p><strong>{{ countByStatus('Rejected') }}</strong></div>
        </article>
      </section>

      <section class="requests-panel">
        <div class="toolbar">
          <div class="filter-tabs" aria-label="กรองสถานะคำขอลา">
            <button
              v-for="tab in filters"
              :key="tab.value"
              type="button"
              :class="{ active: activeFilter === tab.value }"
              @click="activeFilter = tab.value"
            >
              {{ tab.label }} <span>{{ countByStatus(tab.value) }}</span>
            </button>
          </div>
          <label class="search-box">
            <span aria-hidden="true">⌕</span>
            <input v-model.trim="searchQuery" type="search" placeholder="ค้นหารหัสใบลา หรือชื่อพนักงาน" aria-label="ค้นหาคำขอลา">
          </label>
        </div>

        <div v-if="loading" class="loading-state" role="status">กำลังโหลดรายการลา…</div>
        <div v-else-if="!filteredRequests.length" class="empty-state">
          <span aria-hidden="true">▤</span>
          <strong>ไม่พบรายการคำขอลา</strong>
          <p>ลองเปลี่ยนตัวกรอง หรือยื่นใบลาใหม่</p>
        </div>
        <div v-else class="table-scroll">
          <table>
            <thead>
              <tr>
                <th>รหัสใบลา</th>
                <th>พนักงาน</th>
                <th>ประเภทการลา</th>
                <th>วันที่ลา</th>
                <th>เหตุผล</th>
                <th>สถานะ</th>
                <th>ผู้อนุมัติ</th>
                <th>จัดการ</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="request in filteredRequests" :key="request.id">
                <td class="request-code">{{ requestCode(request.id) }}</td>
                <td>
                  <strong>{{ employeeName(request) }}</strong>
                  <small>{{ request.employee_code }}</small>
                </td>
                <td>{{ leaveTypeLabel(request.leave_type) }}</td>
                <td class="date-cell">
                  {{ formatDate(request.start_date) }} – {{ formatDate(request.end_date) }}
                  <small>{{ countDays(request.start_date, request.end_date) }} วัน</small>
                </td>
                <td class="reason-cell" :title="request.reason">{{ request.reason || '—' }}</td>
                <td>
                  <span class="status-badge" :class="`status-${request.status.toLowerCase()}`">
                    {{ statusLabel(request.status) }}
                  </span>
                </td>
                <td>{{ request.reviewed_by ? `ผู้ใช้ #${request.reviewed_by}` : '—' }}</td>
                <td class="action-cell">
                  <button class="secondary-button" type="button" @click="selectedRequest = request">รายละเอียด</button>
                  <template v-if="canReview && request.status === 'Pending'">
                    <button class="approve-button" type="button" :disabled="busyId === request.id" @click="decide(request, 'Approved')">
                      อนุมัติ
                    </button>
                    <button class="reject-button" type="button" :disabled="busyId === request.id" @click="decide(request, 'Rejected')">
                      ปฏิเสธ
                    </button>
                  </template>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>

    <div v-if="requestModalOpen" class="modal-backdrop" @click.self="requestModalOpen = false">
      <section class="modal" role="dialog" aria-modal="true" aria-labelledby="requestModalTitle">
        <header class="modal-header">
          <div><p class="eyebrow">LEAVE REQUEST</p><h2 id="requestModalTitle">ยื่นคำขอลา</h2></div>
          <button class="close-button" type="button" aria-label="ปิด" @click="requestModalOpen = false">×</button>
        </header>
        <form class="modal-form" @submit.prevent="submitRequest">
          <label for="leaveType">ประเภทการลา</label>
          <select id="leaveType" v-model="form.leave_type" required>
            <option value="Annual">ลาพักร้อน</option>
            <option value="Sick">ลาป่วย</option>
            <option value="Personal">ลากิจ</option>
            <option value="Other">อื่น ๆ</option>
          </select>

          <div class="date-grid">
            <div><label for="startDate">วันที่เริ่มลา</label><input id="startDate" v-model="form.start_date" type="date" required></div>
            <div><label for="endDate">วันที่สิ้นสุดลา</label><input id="endDate" v-model="form.end_date" type="date" :min="form.start_date" required></div>
          </div>
          <p class="day-count">จำนวนวันลา <strong>{{ formDays }} วัน</strong></p>

          <label for="leaveReason">เหตุผลการลา</label>
          <textarea id="leaveReason" v-model.trim="form.reason" rows="4" maxlength="1000" required placeholder="ระบุเหตุผลประกอบการขอลา"></textarea>

          <div class="upload-block">
            <span class="upload-label">เอกสารประกอบ <span>ไม่บังคับ</span></span>
            <label class="upload-drop" for="leaveAttachment">
              <span class="upload-icon" aria-hidden="true">↑</span>
              <span class="upload-copy"><strong>เลือกไฟล์เพื่อแนบ</strong><small>PDF, JPG หรือ PNG · ขนาดไม่เกิน 5 MB</small></span>
              <input
                id="leaveAttachment"
                ref="attachmentInput"
                type="file"
                accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                :disabled="saving"
                @change="handleAttachmentChange"
              >
            </label>
            <div v-if="attachment" class="selected-file">
              <span aria-hidden="true">▧</span>
              <span class="selected-file-name">{{ attachment.name }}</span>
              <button type="button" class="remove-file" :disabled="saving" @click="removeAttachment">นำออก</button>
            </div>
          </div>

          <p v-if="formError" class="form-error" role="alert">{{ formError }}</p>
          <footer class="modal-actions">
            <button class="secondary-button" type="button" @click="requestModalOpen = false">ยกเลิก</button>
            <button class="primary-button" type="submit" :disabled="saving">
              {{ saving ? 'กำลังส่ง…' : 'ส่งคำขอลา' }}
            </button>
          </footer>
        </form>
      </section>
    </div>

    <div v-if="selectedRequest" class="modal-backdrop" @click.self="selectedRequest = null">
      <section class="modal detail-modal" role="dialog" aria-modal="true" aria-labelledby="detailTitle">
        <header class="modal-header">
          <div><p class="eyebrow">{{ requestCode(selectedRequest.id) }}</p><h2 id="detailTitle">รายละเอียดคำขอลา</h2></div>
          <button class="close-button" type="button" aria-label="ปิด" @click="selectedRequest = null">×</button>
        </header>
        <div class="detail-body">
          <div class="detail-person">
            <div><small>ผู้ยื่นคำขอ</small><strong>{{ employeeName(selectedRequest) }}</strong><span>{{ selectedRequest.employee_code }}</span></div>
            <span class="status-badge" :class="`status-${selectedRequest.status.toLowerCase()}`">{{ statusLabel(selectedRequest.status) }}</span>
          </div>
          <div class="detail-grid">
            <div><small>ประเภทการลา</small><strong>{{ leaveTypeLabel(selectedRequest.leave_type) }}</strong></div>
            <div><small>จำนวนวัน</small><strong>{{ countDays(selectedRequest.start_date, selectedRequest.end_date) }} วัน</strong></div>
            <div><small>วันที่เริ่มลา</small><strong>{{ formatDate(selectedRequest.start_date) }}</strong></div>
            <div><small>วันที่สิ้นสุด</small><strong>{{ formatDate(selectedRequest.end_date) }}</strong></div>
          </div>
          <div><small class="detail-label">เหตุผลการลา</small><p class="detail-reason">{{ selectedRequest.reason || 'ไม่ได้ระบุเหตุผล' }}</p></div>
          <a
            v-if="selectedRequest.has_attachment"
            class="attachment-download"
            :href="`/projecthub/php_api/leave_attachment.php?id=${encodeURIComponent(selectedRequest.id)}`"
          >ดาวน์โหลดเอกสารแนบ</a>
          <p v-if="selectedRequest.reviewed_by" class="reviewed-note">พิจารณาโดยผู้ใช้ #{{ selectedRequest.reviewed_by }}</p>
        </div>
      </section>
    </div>
  </main>
</template>

<script>
const leaveApi = '/projecthub/php_api/leave_requests.php'

export default {
  name: 'AboutView',
  data() {
    return {
      requests: [],
      loading: false,
      saving: false,
      busyId: null,
      error: '',
      formError: '',
      notice: '',
      unauthorized: false,
      canReview: false,
      activeFilter: 'ALL',
      searchQuery: '',
      requestModalOpen: false,
      selectedRequest: null,
      attachment: null,
      form: { leave_type: 'Annual', start_date: '', end_date: '', reason: '' },
      filters: [
        { value: 'ALL', label: 'ทั้งหมด' },
        { value: 'Pending', label: 'รออนุมัติ' },
        { value: 'Approved', label: 'อนุมัติแล้ว' },
        { value: 'Rejected', label: 'ไม่อนุมัติ' }
      ]
    }
  },
  computed: {
    filteredRequests() {
      const query = this.searchQuery.toLocaleLowerCase()
      return this.requests.filter(request => {
        const statusMatches = this.activeFilter === 'ALL' || request.status === this.activeFilter
        const searchMatches = `${this.requestCode(request.id)} ${request.employee_code} ${this.employeeName(request)} ${this.leaveTypeLabel(request.leave_type)}`.toLocaleLowerCase().includes(query)
        return statusMatches && searchMatches
      })
    },
    formDays() {
      return this.countDays(this.form.start_date, this.form.end_date)
    }
  },
  mounted() {
    this.loadRequests()
  },
  methods: {
    async loadRequests() {
      this.loading = true
      this.error = ''
      this.unauthorized = false
      try {
        const response = await fetch(leaveApi, { credentials: 'include' })
        const result = await response.json()
        if (!response.ok || !result.success) {
          this.unauthorized = response.status === 401
          throw new Error(result.message || 'โหลดรายการลาไม่สำเร็จ')
        }
        if (!Array.isArray(result.data)) throw new Error('ข้อมูลรายการลาที่ได้รับไม่ถูกต้อง')
        this.requests = result.data
        this.canReview = result.can_review === true
      } catch (error) {
        this.error = error.message || 'เชื่อมต่อระบบไม่ได้ กรุณาลองอีกครั้ง'
      } finally {
        this.loading = false
      }
    },
    openRequestForm() {
      this.formError = ''
      this.form = { leave_type: 'Annual', start_date: '', end_date: '', reason: '' }
      this.attachment = null
      this.requestModalOpen = true
    },
    handleAttachmentChange(event) {
      const file = event.target.files[0] || null
      if (!file) return

      const extension = file.name.split('.').pop().toLowerCase()
      if (file.size > 5 * 1024 * 1024) {
        this.formError = 'ไฟล์ต้องมีขนาดไม่เกิน 5 MB'
        event.target.value = ''
        this.attachment = null
        return
      }
      if (!['pdf', 'jpg', 'jpeg', 'png'].includes(extension)) {
        this.formError = 'รองรับเฉพาะไฟล์ PDF, JPG และ PNG'
        event.target.value = ''
        this.attachment = null
        return
      }

      this.formError = ''
      this.attachment = file
    },
    removeAttachment() {
      this.attachment = null
      if (this.$refs.attachmentInput) this.$refs.attachmentInput.value = ''
    },
    async submitRequest() {
      this.formError = ''
      if (this.form.end_date < this.form.start_date) {
        this.formError = 'วันที่สิ้นสุดต้องไม่อยู่ก่อนวันที่เริ่มลา'
        return
      }
      this.saving = true
      try {
        const formData = new FormData()
        Object.entries(this.form).forEach(([key, value]) => formData.append(key, value))
        if (this.attachment) formData.append('attachment', this.attachment)

        const response = await fetch(leaveApi, {
          method: 'POST',
          credentials: 'include',
          body: formData
        })
        let result
        try {
          result = await response.json()
        } catch (error) {
          throw new Error('เซิร์ฟเวอร์ตอบกลับไม่ถูกต้อง กรุณาลองอีกครั้ง')
        }
        if (!response.ok || !result.success) throw new Error(result.message || 'ส่งคำขอลาไม่สำเร็จ')
        this.requestModalOpen = false
        this.notice = 'ส่งคำขอลาเรียบร้อยแล้ว'
        await this.loadRequests()
      } catch (error) {
        this.formError = error.message || 'เชื่อมต่อระบบไม่ได้ กรุณาลองอีกครั้ง'
      } finally {
        this.saving = false
      }
    },
    async decide(request, status) {
      this.error = ''
      this.notice = ''
      this.busyId = request.id
      try {
        const response = await fetch(`${leaveApi}?id=${encodeURIComponent(request.id)}`, {
          method: 'PUT',
          credentials: 'include',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ status })
        })
        const result = await response.json()
        if (!response.ok || !result.success) throw new Error(result.message || 'บันทึกผลการพิจารณาไม่สำเร็จ')
        this.notice = status === 'Approved' ? 'อนุมัติคำขอลาเรียบร้อยแล้ว' : 'ปฏิเสธคำขอลาเรียบร้อยแล้ว'
        await this.loadRequests()
      } catch (error) {
        this.error = error.message || 'เชื่อมต่อระบบไม่ได้ กรุณาลองอีกครั้ง'
      } finally {
        this.busyId = null
      }
    },
    countByStatus(status) {
      if (status === 'ALL') return this.requests.length
      return this.requests.filter(request => request.status === status).length
    },
    requestCode(id) {
      return `LV-${String(id).padStart(4, '0')}`
    },
    employeeName(request) {
      return `${request.first_name || ''} ${request.last_name || ''}`.trim() || 'พนักงาน'
    },
    leaveTypeLabel(type) {
      return { Annual: 'ลาพักร้อน', Sick: 'ลาป่วย', Personal: 'ลากิจ', Other: 'อื่น ๆ' }[type] || type
    },
    statusLabel(status) {
      return { Pending: 'รออนุมัติ', Approved: 'อนุมัติแล้ว', Rejected: 'ไม่อนุมัติ' }[status] || status
    },
    countDays(startDate, endDate) {
      if (!startDate || !endDate) return 0
      const start = Date.parse(`${startDate}T00:00:00Z`)
      const end = Date.parse(`${endDate}T00:00:00Z`)
      if (!Number.isFinite(start) || !Number.isFinite(end) || end < start) return 0
      return Math.floor((end - start) / 86400000) + 1
    },
    formatDate(value) {
      if (!value) return '—'
      return new Intl.DateTimeFormat('th-TH', { day: 'numeric', month: 'short', year: 'numeric' })
        .format(new Date(`${value}T00:00:00`))
    }
  }
}
</script>

<style scoped>
.leave-page {
  min-height: calc(100vh - 90px);
  padding: 32px 18px 56px;
  background: #f4f7fb;
  color: #1e293b;
  text-align: left;
  font-family: Arial, "Noto Sans Thai", sans-serif;
}

.page-container { width: min(100%, 1280px); margin: 0 auto; }
.page-heading { display: flex; align-items: center; justify-content: space-between; gap: 20px; margin-bottom: 22px; }
.page-heading h1 { margin: 4px 0 5px; color: #0f172a; font-size: clamp(22px, 3vw, 30px); }
.page-heading p:not(.eyebrow) { margin: 0; color: #64748b; font-size: 13px; }
.eyebrow { margin: 0; color: #2563eb; font-size: 10px; font-weight: 800; letter-spacing: .12em; }
.primary-button, .secondary-button, .approve-button, .reject-button { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 13px; border: 1px solid transparent; border-radius: 9px; cursor: pointer; font: inherit; font-size: 12px; font-weight: 700; white-space: nowrap; }
.primary-button { background: #2563eb; color: #fff; }
.primary-button:hover:not(:disabled) { background: #1d4ed8; }
.primary-button:disabled, .approve-button:disabled, .reject-button:disabled { cursor: wait; opacity: .6; }
.secondary-button { border-color: #dbe2ea; background: #fff; color: #475569; }
.secondary-button:hover { background: #f1f5f9; }
.summary-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 20px; }
.summary-card { display: flex; align-items: center; gap: 13px; padding: 18px; border: 1px solid #e2e8f0; border-radius: 14px; background: #fff; box-shadow: 0 2px 8px #0f172a08; }
.summary-icon { display: grid; width: 40px; height: 40px; flex: 0 0 40px; place-items: center; border-radius: 12px; font-size: 19px; font-weight: 700; }
.total-icon { background: #eff6ff; color: #2563eb; }
.pending-icon { background: #fffbeb; color: #b45309; }
.approved-icon { background: #f0fdf4; color: #15803d; }
.rejected-icon { background: #fff1f2; color: #be123c; }
.summary-card p { margin: 0 0 4px; color: #64748b; font-size: 11px; }
.summary-card strong { color: #0f172a; font-size: 22px; }
.requests-panel { overflow: hidden; border: 1px solid #e2e8f0; border-radius: 15px; background: #fff; box-shadow: 0 2px 8px #0f172a08; }
.toolbar { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 15px; border-bottom: 1px solid #eef2f7; }
.filter-tabs { display: flex; flex-wrap: wrap; gap: 4px; padding: 4px; border-radius: 10px; background: #f1f5f9; }
.filter-tabs button { padding: 7px 10px; border: 0; border-radius: 7px; background: transparent; color: #64748b; cursor: pointer; font: inherit; font-size: 11px; white-space: nowrap; }
.filter-tabs button.active { background: #fff; color: #1d4ed8; box-shadow: 0 1px 3px #0f172a20; font-weight: 700; }
.filter-tabs button span { margin-left: 2px; }
.search-box { display: flex; width: min(100%, 310px); align-items: center; gap: 8px; padding: 0 10px; border: 1px solid #dbe2ea; border-radius: 9px; color: #94a3b8; }
.search-box input { width: 100%; padding: 9px 0; border: 0; outline: 0; color: #1e293b; font: inherit; font-size: 11px; }
.table-scroll { overflow-x: auto; }
table { width: 100%; min-width: 1000px; border-collapse: collapse; text-align: left; font-size: 11px; }
th { padding: 12px; background: #f8fafc; color: #64748b; font-size: 10px; font-weight: 700; white-space: nowrap; }
td { padding: 12px; border-top: 1px solid #f1f5f9; color: #475569; vertical-align: middle; }
tbody tr:hover { background: #f8fafc; }
td strong { display: block; color: #0f172a; }
td small { display: block; margin-top: 4px; color: #94a3b8; font-size: 10px; }
.request-code { color: #1d4ed8; font-family: Consolas, monospace; font-weight: 700; white-space: nowrap; }
.date-cell { white-space: nowrap; }
.reason-cell { max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.status-badge { display: inline-block; padding: 4px 8px; border: 1px solid transparent; border-radius: 20px; font-size: 10px; font-weight: 700; white-space: nowrap; }
.status-pending { border-color: #fde68a; background: #fffbeb; color: #92400e; }
.status-approved { border-color: #bbf7d0; background: #f0fdf4; color: #15803d; }
.status-rejected { border-color: #fecdd3; background: #fff1f2; color: #be123c; }
.action-cell { min-width: 220px; white-space: nowrap; }
.action-cell button { margin: 2px; }
.approve-button { background: #16a34a; color: #fff; }
.approve-button:hover:not(:disabled) { background: #15803d; }
.reject-button { border-color: #fecdd3; background: #fff1f2; color: #be123c; }
.loading-state, .empty-state { display: grid; min-height: 190px; place-content: center; justify-items: center; gap: 8px; color: #64748b; text-align: center; }
.empty-state > span { color: #94a3b8; font-size: 24px; }
.empty-state strong { color: #334155; font-size: 13px; }
.empty-state p { margin: 0; font-size: 11px; }
.alert { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin: 0 0 14px; padding: 12px 14px; border: 1px solid; border-radius: 10px; font-size: 12px; }
.error-alert { border-color: #fecdd3; background: #fff1f2; color: #9f1239; }
.success-alert { border-color: #bbf7d0; background: #f0fdf4; color: #166534; }
.text-button { padding: 4px; border: 0; background: transparent; color: #1d4ed8; cursor: pointer; font: inherit; font-weight: 700; white-space: nowrap; }
.modal-backdrop { position: fixed; inset: 0; z-index: 20; display: grid; place-items: center; overflow-y: auto; padding: 18px; background: #0f172a88; backdrop-filter: blur(3px); }
.modal { width: min(100%, 540px); max-height: calc(100vh - 36px); overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 17px; background: #fff; box-shadow: 0 24px 80px #0f172a35; }
.detail-modal { max-width: 510px; }
.modal-header { position: sticky; top: 0; z-index: 1; display: flex; align-items: center; justify-content: space-between; padding: 17px 20px; border-bottom: 1px solid #e2e8f0; background: #fff; }
.modal-header h2 { margin: 4px 0 0; color: #0f172a; font-size: 18px; }
.close-button { width: 32px; height: 32px; border: 0; border-radius: 8px; background: #f1f5f9; color: #475569; cursor: pointer; font-size: 21px; }
.modal-form, .detail-body { display: grid; gap: 13px; padding: 20px; }
.modal-form label { display: grid; gap: 6px; color: #475569; font-size: 11px; font-weight: 700; }
.modal-form input, .modal-form select, .modal-form textarea { width: 100%; box-sizing: border-box; padding: 10px 12px; border: 1px solid #dbe2ea; border-radius: 9px; outline: none; background: #f8fafc; color: #0f172a; font: inherit; font-size: 12px; }
.modal-form input:focus, .modal-form select:focus, .modal-form textarea:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px #3b82f620; }
.date-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.day-count { margin: 0; padding: 11px 12px; border-radius: 9px; background: #eff6ff; color: #1e40af; font-size: 11px; }
.day-count strong { float: right; }
.form-error { margin: 0; color: #be123c; font-size: 11px; }
.modal-actions { display: flex; justify-content: flex-end; gap: 8px; padding-top: 12px; border-top: 1px solid #eef2f7; }
.detail-person { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 13px; border: 1px solid #e2e8f0; border-radius: 11px; background: #f8fafc; }
.detail-person > div { display: grid; gap: 4px; }
.detail-person small, .detail-grid small, .detail-label { color: #94a3b8; font-size: 10px; }
.detail-person strong { color: #0f172a; font-size: 13px; }
.detail-person span:not(.status-badge) { color: #64748b; font-size: 10px; }
.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 9px; }
.detail-grid > div { display: grid; gap: 6px; padding: 12px; border-radius: 9px; background: #f8fafc; }
.detail-grid strong { color: #1e293b; font-size: 11px; }
.detail-label { font-weight: 700; }
.detail-reason { margin: 6px 0 0; padding: 12px; border: 1px solid #eef2f7; border-radius: 9px; color: #475569; font-size: 12px; line-height: 1.7; overflow-wrap: anywhere; }
.reviewed-note { margin: 0; padding-top: 10px; border-top: 1px solid #eef2f7; color: #64748b; font-size: 10px; }
.upload-block { display: grid; gap: 8px; }
.upload-label { color: #475569; font-size: 11px; font-weight: 700; }
.upload-label span { margin-left: 5px; color: #94a3b8; font-weight: 400; }
.upload-drop { position: relative; display: flex !important; min-height: 64px; align-items: center; gap: 12px !important; padding: 12px; border: 1px dashed #cbd5e1; border-radius: 10px; background: #f8fafc; cursor: pointer; }
.upload-drop:hover { border-color: #3b82f6; background: #eff6ff; }
.upload-icon { display: grid; width: 36px; height: 36px; flex: 0 0 36px; place-items: center; border-radius: 10px; background: #dbeafe; color: #2563eb; font-size: 20px; }
.upload-copy { display: grid; gap: 4px; }
.upload-copy strong { color: #334155; font-size: 11px; }
.upload-copy small { color: #94a3b8; font-size: 10px; }
.upload-drop input { position: absolute; width: 1px; height: 1px; overflow: hidden; opacity: 0; }
.selected-file { display: flex; align-items: center; gap: 8px; padding: 8px 10px; border: 1px solid #dbeafe; border-radius: 8px; background: #eff6ff; color: #2563eb; font-size: 11px; }
.selected-file-name { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.remove-file { border: 0; background: transparent; color: #64748b; cursor: pointer; font: inherit; font-size: 10px; }
.attachment-download { display: inline-flex; width: fit-content; padding: 8px 11px; border-radius: 8px; background: #eff6ff; color: #1d4ed8; font-size: 11px; font-weight: 700; text-decoration: none; }
.attachment-download:hover { background: #dbeafe; }

@media (max-width: 760px) {
  .summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .toolbar { align-items: stretch; flex-direction: column; }
  .search-box { width: 100%; box-sizing: border-box; }
}
@media (max-width: 520px) {
  .leave-page { padding: 22px 12px 36px; }
  .page-heading { align-items: flex-start; flex-direction: column; }
  .date-grid { grid-template-columns: 1fr; }
  .summary-card { padding: 13px; }
  .summary-icon { width: 34px; height: 34px; flex-basis: 34px; }
}
</style>
