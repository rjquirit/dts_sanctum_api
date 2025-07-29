import { bindLogin, bindRegister } from './modules/auth.js';
import { loadUsers, bindUserActions } from './modules/userCrud.js';

document.addEventListener('DOMContentLoaded', () => {
  const path = window.location.pathname;

  if (path.includes('/login')) {
    bindLogin('#loginForm');
  } else if (path.includes('/register')) {
    bindRegister('#registerForm');
  } else if (path === '/' || path.includes('/dashboard')) {
    loadUsers('#usersTable tbody');
    bindUserActions('#userForm', '#usersTable tbody');
  }

  // Register PWA service worker
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/js/sw.js');
  }
});
