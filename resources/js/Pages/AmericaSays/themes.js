// America Says Game Themes Configuration

export const themes = {
    christmas: {
        name: 'Christmas',
        colors: {
            primary: '#C41E3A',      // Christmas red
            secondary: '#165B33',     // Christmas green
            accent: '#FFD700',        // Gold
            background: '#F5F5F5',    // Light snow
            text: '#2C3E50',
            answerBox: '#FFFFFF',
            answerBoxRevealed: '#FFD700',
            timerWarning: '#FF6B6B',
        },
        fonts: {
            question: '"Mountains of Christmas", cursive',
            answer: '"Raleway", sans-serif',
        },
        effects: {
            backgroundPattern: 'snowflakes',
            revealAnimation: 'sparkle',
            sounds: true,
        },
        images: {
            backgroundImage: null, // Optional: path to background image
            decorations: ['snowflake', 'ornament', 'star'],
        }
    },

    default: {
        name: 'Classic',
        colors: {
            primary: '#2563EB',       // Blue
            secondary: '#7C3AED',     // Purple
            accent: '#F59E0B',        // Amber
            background: '#F9FAFB',    // Gray
            text: '#111827',
            answerBox: '#FFFFFF',
            answerBoxRevealed: '#FCD34D',
            timerWarning: '#EF4444',
        },
        fonts: {
            question: '"Inter", sans-serif',
            answer: '"Inter", sans-serif',
        },
        effects: {
            backgroundPattern: 'none',
            revealAnimation: 'pulse',
            sounds: false,
        },
        images: {
            backgroundImage: null,
            decorations: [],
        }
    },

    halloween: {
        name: 'Halloween',
        colors: {
            primary: '#FF6B35',       // Orange
            secondary: '#4A0E4E',     // Purple
            accent: '#F7931E',        // Bright orange
            background: '#1A1A1A',    // Dark
            text: '#F5F5F5',
            answerBox: '#2D2D2D',
            answerBoxRevealed: '#FF6B35',
            timerWarning: '#DC2626',
        },
        fonts: {
            question: '"Creepster", cursive',
            answer: '"Raleway", sans-serif',
        },
        effects: {
            backgroundPattern: 'spiderwebs',
            revealAnimation: 'spooky',
            sounds: true,
        },
        images: {
            backgroundImage: null,
            decorations: ['bat', 'pumpkin', 'ghost'],
        }
    },

    sports: {
        name: 'Sports',
        colors: {
            primary: '#047857',       // Green (field)
            secondary: '#1E40AF',     // Blue
            accent: '#FBBF24',        // Yellow
            background: '#F3F4F6',
            text: '#111827',
            answerBox: '#FFFFFF',
            answerBoxRevealed: '#34D399',
            timerWarning: '#EF4444',
        },
        fonts: {
            question: '"Bebas Neue", sans-serif',
            answer: '"Roboto", sans-serif',
        },
        effects: {
            backgroundPattern: 'stadium',
            revealAnimation: 'score',
            sounds: true,
        },
        images: {
            backgroundImage: null,
            decorations: ['trophy', 'medal', 'star'],
        }
    }
};

// Helper function to get theme by key
export const getTheme = (themeKey = 'default') => {
    return themes[themeKey] || themes.default;
};

// Helper to get theme from event data
export const getThemeFromEvent = (event) => {
    if (!event) return themes.default;

    const category = event.category?.toLowerCase() || '';

    if (category.includes('christmas') || category.includes('holiday')) {
        return themes.christmas;
    } else if (category.includes('halloween') || category.includes('spooky')) {
        return themes.halloween;
    } else if (category.includes('sport')) {
        return themes.sports;
    }

    return themes.default;
};
