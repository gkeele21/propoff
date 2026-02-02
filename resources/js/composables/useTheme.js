import { ref, watch } from 'vue';

/**
 * Theme Composable
 *
 * Manages the user's theme preference (accent color) and background mode.
 * Persists to localStorage and applies theme/mode classes to <html>.
 *
 * Available themes: 'green', 'blue', 'orange'
 * Available background modes: 'slate', 'cream'
 */

const STORAGE_KEY = 'propoff-theme';
const STORAGE_KEY_BG = 'propoff-bg-mode';
const DEFAULT_THEME = 'green';
const DEFAULT_BG_MODE = 'slate';
const VALID_THEMES = ['green', 'blue', 'orange'];
const VALID_BG_MODES = ['slate', 'cream'];

// Shared reactive state (singleton pattern)
const currentTheme = ref(DEFAULT_THEME);
const currentBgMode = ref(DEFAULT_BG_MODE);
const isInitialized = ref(false);

/**
 * Apply theme class to the document
 */
function applyTheme(theme) {
    if (typeof document === 'undefined') return;

    const html = document.documentElement;

    // Remove all theme classes
    VALID_THEMES.forEach((t) => {
        html.classList.remove(`theme-${t}`);
    });

    // Add the new theme class
    html.classList.add(`theme-${theme}`);
}

/**
 * Apply background mode class to the document
 */
function applyBgMode(mode) {
    if (typeof document === 'undefined') return;

    const html = document.documentElement;

    // Remove all bg mode classes
    VALID_BG_MODES.forEach((m) => {
        html.classList.remove(`bg-mode-${m}`);
    });

    // Add the new bg mode class
    html.classList.add(`bg-mode-${mode}`);
}

/**
 * Load theme from localStorage
 */
function loadTheme() {
    if (typeof localStorage === 'undefined') return DEFAULT_THEME;

    const stored = localStorage.getItem(STORAGE_KEY);
    if (stored && VALID_THEMES.includes(stored)) {
        return stored;
    }
    return DEFAULT_THEME;
}

/**
 * Save theme to localStorage
 */
function saveTheme(theme) {
    if (typeof localStorage === 'undefined') return;
    localStorage.setItem(STORAGE_KEY, theme);
}

/**
 * Load background mode from localStorage
 */
function loadBgMode() {
    if (typeof localStorage === 'undefined') return DEFAULT_BG_MODE;

    const stored = localStorage.getItem(STORAGE_KEY_BG);
    if (stored && VALID_BG_MODES.includes(stored)) {
        return stored;
    }
    return DEFAULT_BG_MODE;
}

/**
 * Save background mode to localStorage
 */
function saveBgMode(mode) {
    if (typeof localStorage === 'undefined') return;
    localStorage.setItem(STORAGE_KEY_BG, mode);
}

/**
 * Initialize theme and background mode on first use
 */
function initializeTheme() {
    if (isInitialized.value) return;

    const storedTheme = loadTheme();
    currentTheme.value = storedTheme;
    applyTheme(storedTheme);

    const storedBgMode = loadBgMode();
    currentBgMode.value = storedBgMode;
    applyBgMode(storedBgMode);

    isInitialized.value = true;
}

/**
 * useTheme composable
 *
 * @returns {Object} Theme utilities
 * @property {Ref<string>} theme - Current theme ('green', 'blue', 'orange')
 * @property {Function} setTheme - Set the theme
 * @property {Array<string>} themes - Available theme options
 * @property {Function} cycleTheme - Cycle to the next theme
 * @property {Ref<string>} bgMode - Current background mode ('slate', 'cream')
 * @property {Function} setBgMode - Set the background mode
 * @property {Array<string>} bgModes - Available background mode options
 */
export function useTheme() {
    // Initialize on first composable use
    initializeTheme();

    // Watch for theme changes
    watch(currentTheme, (newTheme) => {
        if (VALID_THEMES.includes(newTheme)) {
            applyTheme(newTheme);
            saveTheme(newTheme);
        }
    });

    // Watch for background mode changes
    watch(currentBgMode, (newMode) => {
        if (VALID_BG_MODES.includes(newMode)) {
            applyBgMode(newMode);
            saveBgMode(newMode);
        }
    });

    /**
     * Set the current theme
     * @param {string} theme - Theme name ('green', 'blue', 'orange')
     */
    function setTheme(theme) {
        if (VALID_THEMES.includes(theme)) {
            currentTheme.value = theme;
        } else {
            console.warn(`Invalid theme: ${theme}. Valid themes: ${VALID_THEMES.join(', ')}`);
        }
    }

    /**
     * Cycle to the next theme in the list
     */
    function cycleTheme() {
        const currentIndex = VALID_THEMES.indexOf(currentTheme.value);
        const nextIndex = (currentIndex + 1) % VALID_THEMES.length;
        currentTheme.value = VALID_THEMES[nextIndex];
    }

    /**
     * Set the current background mode
     * @param {string} mode - Background mode ('slate', 'cream')
     */
    function setBgMode(mode) {
        if (VALID_BG_MODES.includes(mode)) {
            currentBgMode.value = mode;
        } else {
            console.warn(`Invalid bg mode: ${mode}. Valid modes: ${VALID_BG_MODES.join(', ')}`);
        }
    }

    return {
        theme: currentTheme,
        setTheme,
        themes: VALID_THEMES,
        cycleTheme,
        bgMode: currentBgMode,
        setBgMode,
        bgModes: VALID_BG_MODES,
    };
}

export default useTheme;
