<script setup lang="ts">
import { ref } from 'vue'

interface Link {
  id: number
  code: string
  url: string
};

const domain = location.host;

// hard coded test row
const links = ref<Link[]>([
  {
    id: 1,
    code: '123abc67',
    url: 'https://www.google.com',
  },
]);

const editingId = ref<number | null>(null);
const draftCode = ref('');
const vFocus = { mounted: (element: HTMLElement) => element.focus() };

function startEdit(link: Link) {
  editingId.value = link.id;
  draftCode.value = link.code;
}

function saveEdit(link: Link) {
  const code = draftCode.value.trim();
  if(!code) return;

  link.code = code;
  editingId.value = null;
}

function cancelEdit() {
  editingId.value = null;
}
</script>

<template>
  <h1 class="title-text">your links</h1>

  <div class="table-wrapper">
    <table class="links-table">
      <!-- table header columns-->
      <thead>
        <tr>
          <th class="id-column">ID</th>
          <th>Short Link</th>
          <th>Original Link</th>
          <th>Actions</th>
        </tr>
      </thead>

      <!-- all the users links -->
      <tbody>
        <tr v-for="link in links" :key="link.id">
          <!-- link id -->
          <td>{{ link.id }}</td>

          <!-- the shortened link -->
          <td>
            <div class="short-link">
              <span>{{ domain }}/</span>
              <span v-if="editingId === link.id" class="code-edit">
                <input class="code-input" v-model="draftCode" v-focus maxlength="64" @keyup.enter="saveEdit(link)" @keyup.esc="cancelEdit">
                <span class="edit-buttons">
                  <button class="action-button save-button pi pi-check" @click="saveEdit(link)"></button>
                  <button class="action-button pi pi-times" @click="cancelEdit"></button>
                </span>
              </span>
              <template v-else>{{ link.code }}</template>
            </div>
          </td>

          <!-- the unshortened link -->
          <td>
            <div class="scroll-cell">{{ link.url }}</div>
          </td>

          <!-- the action icons -->
          <td>
            <div class="actions">
              <button class="action-button pi pi-pencil" @click="startEdit(link)"></button>
              <button class="action-button delete-button pi pi-trash"></button>
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

.delete-button {
  color: crimson;
}

.delete-button:hover {
  color: color-mix(in oklab, crimson, white 20%);
}
</style>
