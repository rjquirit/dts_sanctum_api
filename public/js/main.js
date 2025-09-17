import { bindLogin, bindRegister, bindGoogleLogin } from './modules/auth.js';
//import { loadUsers, bindUserActions } from './modules/userCrud.js';
import { logout } from './modules/logout.js';


document.addEventListener('DOMContentLoaded', () => {
  const path = window.location.pathname;

  if (path.includes('/login')) {
    bindLogin('#loginForm');
    bindGoogleLogin('#GoogleLoginButton');
  } else if (path.includes('/register')) {
    bindRegister('#registerForm');
  } 
//   else if (path === '/' || path.includes('/incoming')) {
//     loadUsers('#usersTable tbody');
//     bindUserActions('#userForm', '#usersTable tbody');
//   }

  // Bind logout button
    const logoutBtn = document.getElementById('logoutBtn');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', async (e) => {
      e.preventDefault();
      const confirmLogout = confirm("Are you sure you want to logout?");
      if (confirmLogout) {
        await logout();
      }
    });
}

  // Register PWA service worker - improved version
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('/sw.js', {
        scope: '/'
      })
      .then(registration => {
        console.log('SW: Registration successful with scope:', registration.scope);
        
        // Handle updates
        registration.addEventListener('updatefound', () => {
          const newWorker = registration.installing;
          newWorker.addEventListener('statechange', () => {
            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
              console.log('SW: New version available, will activate on next page load');
            }
          });
        });
      })
      .catch(error => {
        console.error('SW: Registration failed:', error);
      });
    });
  } else {
    console.log('SW: Service workers not supported');
  }
});

// Navigation and responsive behavior handler
export class AppNavigation {
  constructor() {
      this.init();
      this.setupEventListeners();
      this.handleResponsiveNavigation();
  }

  init() {
      // Initialize the app
      this.showAppContainer();
      this.setInitialTab();
  }

  showAppContainer() {
      const appContainer = document.getElementById('appContainer');
      if (appContainer) {
          appContainer.classList.remove('hidden');
      }
  }

  setInitialTab() {
      // Set incoming as the initial active tab
      this.switchTab('incoming');
  }

  setupEventListeners() {
      // Sidebar navigation events
    const sidebarLinks = document.querySelectorAll('.sidebar .nav-link');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            // Don't prevent default navigation
            const tabId = link.getAttribute('data-tab');
            if (tabId) {
                e.preventDefault();
                this.switchTab(tabId);
            }
            // If no data-tab attribute, let the href handle navigation
        });
    });

      // Bottom navigation events
      const bottomNavItems = document.querySelectorAll('.bottom-nav .nav-item');
      bottomNavItems.forEach(item => {
          item.addEventListener('click', () => {
              const tabId = item.getAttribute('data-tab');
              this.switchTab(tabId);
          });
      });

      // Window resize event for responsive handling
      window.addEventListener('resize', () => {
          this.handleResponsiveNavigation();
      });

      // Orientation change event
      window.addEventListener('orientationchange', () => {
          setTimeout(() => {
              this.handleResponsiveNavigation();
          }, 100);
      });
  }

  switchTab(tabId) {
      // Hide all tab contents
      const allTabs = document.querySelectorAll('.tab-content');
      allTabs.forEach(tab => {
          tab.classList.remove('active');
      });

      // Remove active class from all navigation items
      const allNavItems = document.querySelectorAll('.nav-link, .nav-item');
      allNavItems.forEach(item => {
          item.classList.remove('active');
      });

      // Show selected tab
      const selectedTab = document.getElementById(tabId);
      if (selectedTab) {
          selectedTab.classList.add('active');
      }

      // Add active class to corresponding navigation items
      const activeNavItems = document.querySelectorAll(`[data-tab="${tabId}"]`);
      activeNavItems.forEach(item => {
          item.classList.add('active');
      });

      // Update page title
      this.updatePageTitle(tabId);
  }

  updatePageTitle(tabId) {
      const titles = {
          'incoming': 'Incoming',
          'outgoing': 'Outgoing',
          'forward': 'Forward',
          'pending': 'Pending',
          'archive': 'Archive'
      };

      const title = titles[tabId] || 'Incoming';
      document.title = `DepEd ROX - ${title}`;
  }

  handleResponsiveNavigation() {
      const sidebar = document.getElementById('sidebar');
      const bottomNav = document.getElementById('bottomNav');
      const mainContent = document.querySelector('.main-content');

      if (!sidebar || !bottomNav || !mainContent) return;

      const isLandscape = window.innerHeight < window.innerWidth;
      const isMobile = window.innerWidth <= 768;
      const isTablet = window.innerWidth > 768 && window.innerWidth <= 1024;

      // Desktop or landscape orientation
      if (isLandscape && window.innerHeight >= 500) {
          this.showDesktopNavigation();
      }
      // Portrait tablet or mobile
      else if (!isLandscape || isMobile) {
          this.showMobileNavigation();
      }
      // Large screens
      else if (window.innerWidth > 1024) {
          this.showDesktopNavigation();
      }
      // Fallback for edge cases
      else {
          this.showMobileNavigation();
      }
  }

  showDesktopNavigation() {
      const sidebar = document.getElementById('sidebar');
      const bottomNav = document.getElementById('bottomNav');
      const mainContent = document.querySelector('.main-content');

      if (sidebar) sidebar.style.display = 'block';
      if (bottomNav) bottomNav.style.display = 'none';
      if (mainContent) {
          mainContent.style.marginLeft = 'var(--sidebar-width)';
          mainContent.style.marginBottom = '0';
      }
  }

  showMobileNavigation() {
      const sidebar = document.getElementById('sidebar');
      const bottomNav = document.getElementById('bottomNav');
      const mainContent = document.querySelector('.main-content');

      if (sidebar) sidebar.style.display = 'none';
      if (bottomNav) {
          bottomNav.style.display = 'grid';
          bottomNav.style.gridTemplateColumns = 'repeat(4, 1fr)';
      }
      if (mainContent) {
          mainContent.style.marginLeft = '0';
          mainContent.style.marginBottom = 'var(--bottom-nav-height)';
      }
  }

  // Public method to switch tabs (can be called from outside)
  static switchTab(tabId) {
      const instance = window.appNavigation;
      if (instance) {
          instance.switchTab(tabId);
      }
  }
}

// Logout function
// function logout() {
//   if (confirm('Are you sure you want to logout?')) {
//       // Show loading state
//       const logoutBtn = document.querySelector('button[onclick="logout()"]');
//       if (logoutBtn) {
//           logoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging out...';
//           logoutBtn.disabled = true;
//       }

//       // Simulate logout process (replace with actual logout logic)
//       setTimeout(() => {
//           // Replace this with your actual logout endpoint
//           window.location.href = '/logout';
//       }, 1000);
//   }
// }

// Utility functions
const AppUtils = {
  // Show loading spinner
  showLoading(containerId) {
      const container = document.getElementById(containerId);
      if (container) {
          container.innerHTML = '<div class="spinner"></div>';
      }
  },

  // Hide loading spinner
  hideLoading(containerId, content = '') {
      const container = document.getElementById(containerId);
      if (container) {
          container.innerHTML = content;
      }
  },

  // Show toast notification
  showToast(message, type = 'info') {
      // Create toast element
      const toast = document.createElement('div');
      toast.className = `alert alert-${type} position-fixed`;
      toast.style.cssText = `
          top: 80px;
          right: 20px;
          z-index: 9999;
          min-width: 300px;
          animation: slideInRight 0.3s ease;
      `;
      toast.innerHTML = `
          <div class="d-flex justify-content-between align-items-center">
              <span>${message}</span>
              <button type="button" class="btn-close" onclick="this.parentElement.parentElement.remove()"></button>
          </div>
      `;

      // Add to body
      document.body.appendChild(toast);

      // Auto remove after 5 seconds
      setTimeout(() => {
          if (toast.parentElement) {
              toast.remove();
          }
      }, 5000);
  },

  // Format date
  formatDate(date) {
      const options = { 
          year: 'numeric', 
          month: 'short', 
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
      };
      return new Date(date).toLocaleDateString('en-US', options);
  },

  // Debounce function for performance
  debounce(func, wait) {
      let timeout;
      return function executedFunction(...args) {
          const later = () => {
              clearTimeout(timeout);
              func(...args);
          };
          clearTimeout(timeout);
          timeout = setTimeout(later, wait);
      };
  },

  // Check if device is mobile
  isMobile() {
      return window.innerWidth <= 768;
  },

  // Check if device is in portrait mode
  isPortrait() {
      return window.innerHeight > window.innerWidth;
  },

  // Smooth scroll to element
  scrollToElement(elementId) {
      const element = document.getElementById(elementId);
      if (element) {
          element.scrollIntoView({ 
              behavior: 'smooth',
              block: 'start'
          });
      }
  }
};

// Form handling utilities
const FormUtils = {
  // Validate form fields
  validateForm(formId) {
      const form = document.getElementById(formId);
      if (!form) return false;

      const requiredFields = form.querySelectorAll('[required]');
      let isValid = true;

      requiredFields.forEach(field => {
          if (!field.value.trim()) {
              field.classList.add('is-invalid');
              isValid = false;
          } else {
              field.classList.remove('is-invalid');
          }
      });

      return isValid;
  },

  // Clear form
  clearForm(formId) {
      const form = document.getElementById(formId);
      if (form) {
          form.reset();
          // Remove validation classes
          form.querySelectorAll('.is-invalid, .is-valid').forEach(field => {
              field.classList.remove('is-invalid', 'is-valid');
          });
      }
  },

  // Get form data as object
  getFormData(formId) {
      const form = document.getElementById(formId);
      if (!form) return {};

      const formData = new FormData(form);
      const data = {};
      
      for (let [key, value] of formData.entries()) {
          data[key] = value;
      }
      
      return data;
  }
};

// API utilities
const ApiUtils = {
  // Set CSRF token for requests
  setCsrfToken() {
      const token = document.querySelector('meta[name="csrf-token"]');
      if (token) {
          return token.getAttribute('content');
      }
      return null;
  },

  // Make API request
  async makeRequest(url, options = {}) {
      const defaultOptions = {
          headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': this.setCsrfToken(),
              'Accept': 'application/json'
          }
      };

      const mergedOptions = { ...defaultOptions, ...options };
      
      try {
          const response = await fetch(url, mergedOptions);
          
          if (!response.ok) {
              throw new Error(`HTTP error! status: ${response.status}`);
          }
          
          return await response.json();
      } catch (error) {
          console.error('API request failed:', error);
          AppUtils.showToast('Request failed. Please try again.', 'danger');
          throw error;
      }
  },

  // GET request
  async get(url) {
      return this.makeRequest(url, { method: 'GET' });
  },

  // POST request
  async post(url, data) {
      return this.makeRequest(url, {
          method: 'POST',
          body: JSON.stringify(data)
      });
  },

  // PUT request
  async put(url, data) {
      return this.makeRequest(url, {
          method: 'PUT',
          body: JSON.stringify(data)
      });
  },

  // DELETE request
  async delete(url) {
      return this.makeRequest(url, { method: 'DELETE' });
  }
};

// PWA utilities
const PWAUtils = {
  // Check if PWA is installable
  checkInstallability() {
      let deferredPrompt;

      window.addEventListener('beforeinstallprompt', (e) => {
          e.preventDefault();
          deferredPrompt = e;
          this.showInstallButton();
      });

      return deferredPrompt;
  },

  // Show install button
  showInstallButton() {
      // You can add an install button here
      console.log('PWA can be installed');
  },

  // Register service worker
  registerServiceWorker() {
      if ('serviceWorker' in navigator) {
          navigator.serviceWorker.register('/sw.js')
              .then((registration) => {
                  console.log('SW registered: ', registration);
              })
              .catch((registrationError) => {
                  console.log('SW registration failed: ', registrationError);
              });
      }
  }
};

// Fix 3: Better event listener organization
const initializeApp = () => {
  try {
    // Initialize navigation
    window.appNavigation = new AppNavigation();

    // Initialize PWA features
    PWAUtils.registerServiceWorker();
    PWAUtils.checkInstallability();

    // Form validation setup
    setupFormValidation();
    
    // Internal links setup
    setupInternalLinks();
    
    // History state handling
    setupHistoryStateHandling();
    
    console.log('App initialized successfully');
  } catch (error) {
    console.error('Error initializing app:', error);
  }
};

// Fix 4: Break out form validation setup
const setupFormValidation = () => {
  const forms = document.querySelectorAll('form');
  forms.forEach(form => {
    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
      input.addEventListener('blur', validateInput);
    });
  });
};

const validateInput = function() {
  if (this.hasAttribute('required') && !this.value.trim()) {
    this.classList.add('is-invalid');
  } else {
    this.classList.remove('is-invalid');
    this.classList.add('is-valid');
  }
};

// Fix 5: Organize visibility handling
const handleVisibilityChange = () => {
  if (document.hidden) {
    console.log('App hidden');
  } else {
    console.log('App visible');
    // TODO: Implement data refresh logic if needed
  }
};

// Event listeners
document.addEventListener('DOMContentLoaded', initializeApp);
document.addEventListener('visibilitychange', handleVisibilityChange);

// Fix 6: Export all utilities to window
const appExports = {
  AppNavigation,
  AppUtils,
  FormUtils,
  ApiUtils,
  PWAUtils
};

Object.assign(window, appExports);

// Add to public/js/main.js
const PWAManager = {
    init() {
        if ('serviceWorker' in navigator) {
            // Register service worker immediately
            navigator.serviceWorker.register('/sw.js')
                .then(registration => {
                    console.log('SW registered:', registration.scope);
                })
                .catch(error => {
                    console.error('SW registration failed:', error);
                });

            // Handle install prompt
            let deferredPrompt;
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                deferredPrompt = e;
                this.showInstallButton(deferredPrompt);
            });
        }
    },

    showInstallButton(deferredPrompt) {
        const installButton = document.createElement('button');
        installButton.className = 'btn btn-primary install-button';
        installButton.innerHTML = '<i class="fas fa-download"></i> Install App';
        installButton.addEventListener('click', async () => {
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            console.log(`User response: ${outcome}`);
        });

        // Add to login page header
        const header = document.querySelector('.auth-logo');
        if (header) {
            header.after(installButton);
        }
    }
};

// Initialize PWA
document.addEventListener('DOMContentLoaded', () => {
    PWAManager.init();
});

// Add this before the initializeApp function

const setupInternalLinks = () => {
  // Handle internal navigation links
  document.querySelectorAll('a[data-internal]').forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      const href = link.getAttribute('href');
      if (href) {
        // Update browser history and handle navigation
        history.pushState({}, '', href);
        handleRouteChange();
      }
    });
  });
};

// Add the handleRouteChange function
const handleRouteChange = () => {
  const path = window.location.pathname;
  // Handle different routes here
  if (path.includes('/login')) {
    bindLogin('#loginForm');
    bindGoogleLogin('#GoogleLoginButton');
  } else if (path.includes('/register')) {
    bindRegister('#registerForm');
  }
  // Add more route handlers as needed
};

// Update the initializeApp function to include proper error handling

// Add the missing setupHistoryStateHandling function
const setupHistoryStateHandling = () => {
  window.addEventListener('popstate', () => {
    handleRouteChange();
  });
};