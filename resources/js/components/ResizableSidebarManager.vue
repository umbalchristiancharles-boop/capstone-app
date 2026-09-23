<script setup>
import { onMounted, onBeforeUnmount } from 'vue'

const sidebarSelectors = [
  '.admin-sidebar',
  '.finance-sidebar',
  '.manager-hr-sidebar',
  '.logistics-sidebar',
  '.cashier-right-sidebar',
]

let observer
const attachedSidebars = new WeakSet()

function attachResizeHandle(sidebar) {
  if (attachedSidebars.has(sidebar) || sidebar.querySelector(':scope > .panel-sidebar-resize-handle')) return

  attachedSidebars.add(sidebar)
  const handle = document.createElement('button')
  const isRightSidebar = sidebar.classList.contains('cashier-right-sidebar')
  handle.type = 'button'
  handle.className = `panel-sidebar-resize-handle${isRightSidebar ? ' panel-sidebar-resize-handle--left' : ''}`
  handle.setAttribute('aria-label', 'Resize sidebar')
  handle.title = 'Resize sidebar'
  if (getComputedStyle(sidebar).position === 'static') sidebar.style.position = 'relative'
  sidebar.appendChild(handle)

  handle.addEventListener('pointerdown', (event) => {
    if (sidebar.getAttribute('aria-hidden') === 'true') return

    event.preventDefault()
    const startX = event.clientX
    const startWidth = sidebar.getBoundingClientRect().width
    const direction = isRightSidebar ? -1 : 1
    sidebar.classList.add('panel-sidebar-resizing')

    const resize = (moveEvent) => {
      const nextWidth = startWidth + direction * (moveEvent.clientX - startX)
      const width = Math.min(320, Math.max(120, nextWidth))
      sidebar.style.width = `${width}px`
      sidebar.style.minWidth = `${width}px`
    }

    const stopResize = () => {
      sidebar.classList.remove('panel-sidebar-resizing')
      document.removeEventListener('pointermove', resize)
      document.removeEventListener('pointerup', stopResize)
    }

    document.addEventListener('pointermove', resize)
    document.addEventListener('pointerup', stopResize)
  })
}

function scanSidebars() {
  document.querySelectorAll(sidebarSelectors.join(',')).forEach(attachResizeHandle)
}

onMounted(() => {
  scanSidebars()
  observer = new MutationObserver(scanSidebars)
  observer.observe(document.body, { childList: true, subtree: true })
})

onBeforeUnmount(() => observer?.disconnect())
</script>

<template><span aria-hidden="true"></span></template>

<style>
.panel-sidebar-resize-handle {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  z-index: 30;
  width: 10px;
  padding: 0;
  border: 0;
  background: transparent;
  cursor: col-resize;
}

.panel-sidebar-resize-handle--left {
  right: auto;
  left: 0;
}

.panel-sidebar-resize-handle::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 4px;
  width: 2px;
  height: 42px;
  border-radius: 999px;
  background: rgba(143, 79, 47, 0.38);
  transform: translateY(-50%);
  transition: height 160ms ease, background-color 160ms ease;
}

.panel-sidebar-resize-handle--left::after { left: 4px; }

.panel-sidebar-resize-handle:hover::after,
.panel-sidebar-resize-handle:focus-visible::after {
  height: 64px;
  background: #8f4f2f;
}

.panel-sidebar-resizing,
.panel-sidebar-resizing .panel-sidebar-resize-handle {
  transition: none !important;
}
</style>
