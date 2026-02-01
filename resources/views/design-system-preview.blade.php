<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PropOff Design System - Color Preview</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Figtree', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            padding: 2rem;
            color: #1a1a1a;
        }
        h1 { font-size: 2rem; margin-bottom: 0.5rem; }
        h2 { font-size: 1.5rem; margin: 2rem 0 1rem; color: #1a3490; }
        h3 { font-size: 1.1rem; margin: 1.5rem 0 0.75rem; color: #555; }
        .subtitle { color: #666; margin-bottom: 2rem; }

        /* Color Swatches */
        .color-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .color-swatch {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            background: white;
        }
        .color-box { height: 80px; display: flex; align-items: flex-end; padding: 0.5rem; }
        .color-box span {
            font-size: 0.7rem;
            padding: 2px 6px;
            background: rgba(255,255,255,0.9);
            border-radius: 3px;
            color: #333;
        }
        .color-info { padding: 0.75rem; }
        .color-name { font-weight: 600; font-size: 0.9rem; }
        .color-use { font-size: 0.75rem; color: #666; margin-top: 0.25rem; }

        /* Brand Colors */
        .primary { background: #1a3490; }
        .primary-light { background: #2a4ab0; }
        .primary-dark { background: #0f1f5a; }
        .success { background: #57d025; }
        .success-light { background: #7de052; }
        .success-dark { background: #186916; }
        .warning { background: #f47612; }
        .warning-light { background: #ff9642; }
        .warning-dark { background: #c45a00; }
        .danger { background: #af1919; }
        .danger-light { background: #d93636; }
        .danger-dark { background: #8a1010; }

        /* Gray Options */
        .section-divider { border-top: 2px solid #ddd; margin: 3rem 0; padding-top: 2rem; }
        .gray-comparison { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; }
        @media (max-width: 900px) { .gray-comparison { grid-template-columns: 1fr; } }
        .gray-option { background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        .gray-option h4 { margin-bottom: 1rem; font-size: 1.1rem; }
        .gray-row { display: flex; gap: 0.5rem; margin-bottom: 0.5rem; }
        .gray-chip {
            flex: 1;
            height: 50px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.65rem;
            font-weight: 500;
        }

        /* Cool Grays (blue undertone) */
        .cool-50 { background: #f8fafc; color: #334155; }
        .cool-100 { background: #f1f5f9; color: #334155; }
        .cool-200 { background: #e2e8f0; color: #334155; }
        .cool-300 { background: #cbd5e1; color: #334155; }
        .cool-400 { background: #94a3b8; color: #fff; }
        .cool-500 { background: #64748b; color: #fff; }
        .cool-600 { background: #475569; color: #fff; }
        .cool-700 { background: #334155; color: #fff; }
        .cool-800 { background: #1e293b; color: #fff; }
        .cool-900 { background: #0f172a; color: #fff; }

        /* Warm Grays (brown/yellow undertone) */
        .warm-50 { background: #fafaf9; color: #44403c; }
        .warm-100 { background: #f5f5f4; color: #44403c; }
        .warm-200 { background: #e7e5e4; color: #44403c; }
        .warm-300 { background: #d6d3d1; color: #44403c; }
        .warm-400 { background: #a8a29e; color: #fff; }
        .warm-500 { background: #78716c; color: #fff; }
        .warm-600 { background: #57534e; color: #fff; }
        .warm-700 { background: #44403c; color: #fff; }
        .warm-800 { background: #292524; color: #fff; }
        .warm-900 { background: #1c1917; color: #fff; }

        /* Pure Neutral Grays */
        .neutral-50 { background: #fafafa; color: #404040; }
        .neutral-100 { background: #f5f5f5; color: #404040; }
        .neutral-200 { background: #e5e5e5; color: #404040; }
        .neutral-300 { background: #d4d4d4; color: #404040; }
        .neutral-400 { background: #a3a3a3; color: #fff; }
        .neutral-500 { background: #737373; color: #fff; }
        .neutral-600 { background: #525252; color: #fff; }
        .neutral-700 { background: #404040; color: #fff; }
        .neutral-800 { background: #262626; color: #fff; }
        .neutral-900 { background: #171717; color: #fff; }

        /* UI Preview */
        .ui-preview {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-top: 2rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        .button-row { display: flex; gap: 1rem; flex-wrap: wrap; margin: 1rem 0; }
        .btn {
            padding: 0.625rem 1.25rem;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.875rem;
            border: none;
            cursor: pointer;
            transition: all 0.15s;
        }
        .btn-primary { background: #1a3490; color: white; }
        .btn-primary:hover { background: #2a4ab0; }
        .btn-success { background: #57d025; color: white; }
        .btn-warning { background: #f47612; color: white; }
        .btn-danger { background: #af1919; color: white; }
        .btn-outline { background: transparent; border: 2px solid #1a3490; color: #1a3490; }

        /* Card Preview */
        .card-preview {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 1.5rem;
            margin: 1rem 0;
            max-width: 400px;
        }
        .card-preview.cool { border-color: #e2e8f0; }
        .card-preview.warm { border-color: #e7e5e4; }
        .card-preview.neutral { border-color: #e5e5e5; }
        .card-title { font-weight: 600; margin-bottom: 0.5rem; }
        .card-text { font-size: 0.875rem; margin-bottom: 1rem; }
        .card-text.cool { color: #64748b; }
        .card-text.warm { color: #78716c; }
        .card-text.neutral { color: #737373; }

        /* Status Badges */
        .badge-row { display: flex; gap: 0.5rem; flex-wrap: wrap; margin: 1rem 0; }
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .badge-primary { background: #1a349015; color: #1a3490; }
        .badge-success { background: #57d02520; color: #186916; }
        .badge-warning { background: #f4761220; color: #c45a00; }
        .badge-danger { background: #af191915; color: #af1919; }

        /* Form Preview */
        .form-group { margin: 1rem 0; max-width: 300px; }
        .form-label { display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem; }
        .form-input {
            width: 100%;
            padding: 0.625rem 0.875rem;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 0.875rem;
        }
        .form-input:focus { outline: none; border-color: #1a3490; box-shadow: 0 0 0 3px #1a349020; }

        /* Recommendation */
        .recommendation {
            background: linear-gradient(135deg, #1a3490 0%, #2a4ab0 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            margin: 2rem 0;
        }
        .recommendation h3 { color: white; margin-top: 0; }
        .recommendation ul { margin: 1rem 0 0 1.5rem; }
        .recommendation li { margin: 0.5rem 0; }
    </style>
</head>
<body>
    <h1>PropOff Design System</h1>
    <p class="subtitle">Color Palette Preview</p>

    <h2>Brand Colors (Semantic Names)</h2>
    <div class="color-grid">
        <div class="color-swatch">
            <div class="color-box primary"><span>#1a3490</span></div>
            <div class="color-info">
                <div class="color-name">Primary</div>
                <div class="color-use">Main actions, links, focus</div>
            </div>
        </div>
        <div class="color-swatch">
            <div class="color-box primary-light"><span>#2a4ab0</span></div>
            <div class="color-info">
                <div class="color-name">Primary Light</div>
                <div class="color-use">Hover states, highlights</div>
            </div>
        </div>
        <div class="color-swatch">
            <div class="color-box primary-dark"><span>#0f1f5a</span></div>
            <div class="color-info">
                <div class="color-name">Primary Dark</div>
                <div class="color-use">Active states, emphasis</div>
            </div>
        </div>
        <div class="color-swatch">
            <div class="color-box success"><span>#57d025</span></div>
            <div class="color-info">
                <div class="color-name">Success</div>
                <div class="color-use">Confirmations, positive</div>
            </div>
        </div>
        <div class="color-swatch">
            <div class="color-box success-dark"><span>#186916</span></div>
            <div class="color-info">
                <div class="color-name">Success Dark</div>
                <div class="color-use">Success text on light bg</div>
            </div>
        </div>
        <div class="color-swatch">
            <div class="color-box warning"><span>#f47612</span></div>
            <div class="color-info">
                <div class="color-name">Warning</div>
                <div class="color-use">Caution, attention</div>
            </div>
        </div>
        <div class="color-swatch">
            <div class="color-box danger"><span>#af1919</span></div>
            <div class="color-info">
                <div class="color-name">Danger</div>
                <div class="color-use">Errors, destructive</div>
            </div>
        </div>
    </div>

    <div class="section-divider">
        <h2>Gray Palette Options</h2>
        <p class="subtitle">Compare how each gray family pairs with your brand colors</p>
    </div>

    <div class="gray-comparison">
        <div class="gray-option">
            <h4>🔵 Cool Grays</h4>
            <p style="font-size: 0.85rem; color: #666; margin-bottom: 1rem;">Blue undertone - pairs naturally with blue primary</p>
            <div class="gray-row">
                <div class="gray-chip cool-50">50</div>
                <div class="gray-chip cool-100">100</div>
                <div class="gray-chip cool-200">200</div>
                <div class="gray-chip cool-300">300</div>
                <div class="gray-chip cool-400">400</div>
            </div>
            <div class="gray-row">
                <div class="gray-chip cool-500">500</div>
                <div class="gray-chip cool-600">600</div>
                <div class="gray-chip cool-700">700</div>
                <div class="gray-chip cool-800">800</div>
                <div class="gray-chip cool-900">900</div>
            </div>
            <div class="card-preview cool" style="margin-top: 1rem;">
                <div class="card-title">Event Card Preview</div>
                <p class="card-text cool">Secondary text using cool gray-500. Notice how the blue undertone complements the primary blue.</p>
                <button class="btn btn-primary">View Event</button>
            </div>
        </div>

        <div class="gray-option">
            <h4>🟤 Warm Grays</h4>
            <p style="font-size: 0.85rem; color: #666; margin-bottom: 1rem;">Brown undertone - softer, friendlier feel</p>
            <div class="gray-row">
                <div class="gray-chip warm-50">50</div>
                <div class="gray-chip warm-100">100</div>
                <div class="gray-chip warm-200">200</div>
                <div class="gray-chip warm-300">300</div>
                <div class="gray-chip warm-400">400</div>
            </div>
            <div class="gray-row">
                <div class="gray-chip warm-500">500</div>
                <div class="gray-chip warm-600">600</div>
                <div class="gray-chip warm-700">700</div>
                <div class="gray-chip warm-800">800</div>
                <div class="gray-chip warm-900">900</div>
            </div>
            <div class="card-preview warm" style="margin-top: 1rem;">
                <div class="card-title">Event Card Preview</div>
                <p class="card-text warm">Secondary text using warm gray-500. The warm tones create contrast with the cool blue primary.</p>
                <button class="btn btn-primary">View Event</button>
            </div>
        </div>

        <div class="gray-option">
            <h4>⚪ Pure Neutral</h4>
            <p style="font-size: 0.85rem; color: #666; margin-bottom: 1rem;">No undertone - completely neutral</p>
            <div class="gray-row">
                <div class="gray-chip neutral-50">50</div>
                <div class="gray-chip neutral-100">100</div>
                <div class="gray-chip neutral-200">200</div>
                <div class="gray-chip neutral-300">300</div>
                <div class="gray-chip neutral-400">400</div>
            </div>
            <div class="gray-row">
                <div class="gray-chip neutral-500">500</div>
                <div class="gray-chip neutral-600">600</div>
                <div class="gray-chip neutral-700">700</div>
                <div class="gray-chip neutral-800">800</div>
                <div class="gray-chip neutral-900">900</div>
            </div>
            <div class="card-preview neutral" style="margin-top: 1rem;">
                <div class="card-title">Event Card Preview</div>
                <p class="card-text neutral">Secondary text using neutral gray-500. True neutral lets the brand colors stand out more.</p>
                <button class="btn btn-primary">View Event</button>
            </div>
        </div>
    </div>

    <div class="section-divider">
        <h2>UI Component Preview</h2>
    </div>

    <div class="ui-preview">
        <h3 style="margin-top: 0;">Buttons</h3>
        <div class="button-row">
            <button class="btn btn-primary">Primary Action</button>
            <button class="btn btn-success">Success</button>
            <button class="btn btn-warning">Warning</button>
            <button class="btn btn-danger">Delete</button>
            <button class="btn btn-outline">Secondary</button>
        </div>

        <h3>Status Badges</h3>
        <div class="badge-row">
            <span class="badge badge-primary">Draft</span>
            <span class="badge badge-success">Open</span>
            <span class="badge badge-warning">Locked</span>
            <span class="badge badge-danger">Closed</span>
        </div>

        <h3>Form Input</h3>
        <div class="form-group">
            <label class="form-label">Event Name</label>
            <input type="text" class="form-input" placeholder="Enter event name...">
        </div>
    </div>

    <div class="recommendation">
        <h3>💡 My Recommendation</h3>
        <p>For PropOff with a blue primary color, I suggest <strong>Cool Grays</strong> because:</p>
        <ul>
            <li>The blue undertone creates a cohesive, professional look</li>
            <li>They harmonize with your blue primary without competing</li>
            <li>Used by most modern SaaS applications (Tailwind's default slate)</li>
            <li>Your orange and green accent colors will pop more against cool backgrounds</li>
        </ul>
    </div>

    <div class="section-divider">
        <h2>Proposed Simplified Palette</h2>
        <p class="subtitle">For your use case, you mentioned wanting just 2 grays. Here's what I'd suggest:</p>
    </div>

    <div class="color-grid">
        <div class="color-swatch">
            <div class="color-box" style="background: #ffffff; border: 1px solid #e2e8f0;"><span>#ffffff</span></div>
            <div class="color-info">
                <div class="color-name">White</div>
                <div class="color-use">Backgrounds, cards</div>
            </div>
        </div>
        <div class="color-swatch">
            <div class="color-box" style="background: #f1f5f9;"><span>#f1f5f9</span></div>
            <div class="color-info">
                <div class="color-name">Gray Light</div>
                <div class="color-use">Page backgrounds, disabled</div>
            </div>
        </div>
        <div class="color-swatch">
            <div class="color-box" style="background: #64748b;"><span style="color: white; background: rgba(0,0,0,0.3);">#64748b</span></div>
            <div class="color-info">
                <div class="color-name">Gray Dark</div>
                <div class="color-use">Secondary text, borders</div>
            </div>
        </div>
        <div class="color-swatch">
            <div class="color-box" style="background: #0f172a;"><span style="color: white; background: rgba(0,0,0,0.3);">#0f172a</span></div>
            <div class="color-info">
                <div class="color-name">Black</div>
                <div class="color-use">Primary text, headings</div>
            </div>
        </div>
    </div>

</body>
</html>
