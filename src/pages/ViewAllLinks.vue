<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { csrfToken, getSession } from '../auth'

interface Link {
  link_id: number
  code: string
  link: string
  private: boolean | number
}

const domain = 'localhost:5173'

const links = ref<Link[]>([])
const editingId = ref<number | null>(null)
const draftCode = ref('')
const error = ref('')
const vFocus = { mounted: (element: HTMLElement) => element.focus() }

async function getLinks() {
  try {
    const response = await fetch('/api/shortlink.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken.value
      },
      body: JSON.stringify({
        action: 'getLinks'
      })
    })

    const data = await response.json()

    if (!response.ok) {
      error.value = data.error || 'Failed to retrieve links'
      return
    }

    links.value = data.links
    error.value = ''

  } catch (err) {
    console.error('Failed to retrieve links:', err)
    error.value = 'Unable to connect to the server'
  }
}

function startEdit(link: Link) {
  editingId.value = link.link_id
  draftCode.value = link.code
}

async function saveEdit(link: Link) {
  const code = draftCode.value.trim()
  if (!code || code.length > 24) return

  try {
    const response = await fetch('/api/shortlink.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken.value
      },
      body: JSON.stringify({
        action: 'editLink',
        link_id: link.link_id,
        link: link.link,
        code: code,
        private: Boolean(Number(link.private))
      })
    })

    const data = await response.json()

    if (!response.ok) {
      error.value = data.error || 'Failed to edit link'
      return
    }

    link.code = code
    editingId.value = null
    error.value = ''

  } catch (err) {
    console.error('Failed to edit link:', err)
    error.value = 'Unable to connect to the server'
  }
}

function cancelEdit() {
  editingId.value = null
}

async function togglePrivate(link: Link) {
  const newPrivate = !Boolean(Number(link.private))

  try {
    const response = await fetch('/api/shortlink.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken.value
      },
      body: JSON.stringify({
        action: 'editLink',
        link_id: link.link_id,
        link: link.link,
        code: link.code,
        private: newPrivate
      })
    })

    const data = await response.json()

    if (!response.ok) {
      error.value = data.error || 'Failed to update privacy'
      return
    }

    link.private = newPrivate
    error.value = ''

  } catch (err) {
    console.error('Failed to update privacy:', err)
    error.value = 'Unable to connect to the server'
  }
}

async function deleteLink(link: Link) {
  try {
    const response = await fetch('/api/shortlink.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken.value
      },
      body: JSON.stringify({
        action: 'deleteLink',
        link_id: link.link_id
      })
    })

    const data = await response.json()

    if (!response.ok) {
      error.value = data.error || 'Failed to delete link'
      return
    }

    links.value = links.value.filter(item => item.link_id !== link.link_id)
    error.value = ''

  } catch (err) {
    console.error('Failed to delete link:', err)
    error.value = 'Unable to connect to the server'
  }
}

async function copyLink(link: Link) {
  try {
    await navigator.clipboard.writeText(`http://${domain}/${link.code}`)
    error.value = ''
  } catch (err) {
    console.error('Failed to copy link:', err)
    error.value = 'Failed to copy link'
  }
}

onMounted(async () => {
  try {
    await getSession()
    await getLinks()
  } catch (err) {
    console.error('Failed to initialize links:', err)
    error.value = 'Failed to initialize session'
  }
})
</script>

<template>
  <h1 class="title-text">your links</h1>

  <p v-if="error" class="error">{{ error }}</p>

  <div class="table-wrapper">
    <table class="links-table">
      <!-- table header columns -->
      <thead>
        <tr>
          <th class="id-column">ID</th>
          <th>Short Link</th>
          <th>Original Link</th>
          <th>Actions</th>
        </tr>
      </thead>

      <!-- all the user's links -->
      <tbody>
        <tr v-for="link in links" :key="link.link_id">
          <!-- link id -->
          <td>{{ link.link_id }}</td>

          <!-- the shortened link -->
          <td>
            <div class="short-link">
              <span>{{ domain }}/</span>
              <span v-if="editingId === link.link_id" class="code-edit">
                <input
                  class="code-input"
                  v-model="draftCode"
                  v-focus
                  maxlength="24"
                  @keyup.enter="saveEdit(link)"
                  @keyup.esc="cancelEdit"
                >
                <span class="edit-buttons">
                  <button
                    type="button"
                    class="action-button save-button pi pi-check"
                    @click="saveEdit(link)"
                  ></button>
                  <button
                    type="button"
                    class="action-button pi pi-times"
                    @click="cancelEdit"
                  ></button>
                </span>
              </span>
              <template v-else>{{ link.code }}</template>
            </div>
          </td>

          <!-- the original link -->
          <td>
            <div class="scroll-cell">{{ link.link }}</div>
          </td>

          <!-- action icons -->
          <td>
            <div class="actions">
              <button
                type="button"
                class="action-button pi pi-copy"
                title="Copy shortened link"
                aria-label="Copy shortened link"
                @click="copyLink(link)"
              ></button>

              <button
                type="button"
                class="action-button privacy-button"
                :class="Boolean(Number(link.private)) ? 'pi pi-lock' : 'pi pi-lock-open'"
                :title="Boolean(Number(link.private)) ? 'Private — click to make public' : 'Public — click to make private'"
                :aria-label="Boolean(Number(link.private)) ? 'Make link public' : 'Make link private'"
                @click="togglePrivate(link)"
              ></button>

              <button
                type="button"
                class="action-button pi pi-pencil"
                title="Edit link"
                @click="startEdit(link)"
              ></button>

              <button
                type="button"
                class="action-button delete-button pi pi-trash"
                title="Delete link"
                @click="deleteLink(link)"
              ></button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
.title-text {
  font-size: 5rem;
  color: cornflowerblue;
  user-select: none;
}

.table-wrapper {
  max-width: 100%;
  overflow-x: auto;
  border-radius: 0.4rem;
  box-sizing: border-box;
  border: 2px solid cornflowerblue;
}

.links-table {
  border-collapse: collapse;
}

.links-table th, .links-table td {
  padding: 0.6rem 1rem;
  text-align: left;
  white-space: nowrap;
}

.links-table th {
  background-color: var(--canvas-dark);
  color: cornflowerblue;
}

.links-table td {
  border-top: 1px solid var(--canvas-dark);
  color: gainsboro;
}

.id-column {
  width: 4rem;
}

.short-link {
  width: 20rem;
  display: flex;
  align-items: center;
}

.code-edit {
  position: relative;
  flex: 1;
  margin: -0.3rem 0;
}

.code-input {
  box-sizing: border-box;
  width: 100%;
  height: 1.75rem;
  padding: 0 3.25rem 0 0.4rem;
  border: 1px solid cornflowerblue;
  border-radius: 0.4rem;
  font-size: 1rem;
  color: gainsboro;
}

.edit-buttons {
  position: absolute;
  right: 0.5rem;
  top: 50%;
  transform: translateY(-50%);
  display: flex;
  gap: 0.5rem;
}

.scroll-cell {
  width: 30rem;
  overflow-x: auto;
  scrollbar-width: thin;
}

.actions {
  display: flex;
  justify-content: end;
  gap: 0.75rem;
}

.action-button {
  padding: 0;
  background: none;
  border: none;
  font-size: 1rem;
  color: gray;
  cursor: pointer;
}

.action-button:hover {
  color: gainsboro;
}

.privacy-button {
  color: cornflowerblue;
}

.privacy-button:hover {
  color: whitesmoke;
}

.delete-button {
  color: crimson;
}

.delete-button:hover {
  color: color-mix(in oklab, crimson, white 20%);
}
</style>
