from pathlib import Path
import re
root = Path(r'c:/xampp/htdocs/CIVICSOLVE')
patches = {
    'index.html': ('assets/css/theme.css', 'assets/js/theme-toggle.js'),
    'login.html': ('assets/css/theme.css', 'assets/js/theme-toggle.js'),
    'register.html': ('assets/css/theme.css', 'assets/js/theme-toggle.js'),
    'user/home.php': ('../assets/css/theme.css', '../assets/js/theme-toggle.js'),
    'user/profile.php': ('../assets/css/theme.css', '../assets/js/theme-toggle.js'),
    'user/submit_issue.php': ('../assets/css/theme.css', '../assets/js/theme-toggle.js'),
    'user/view_status.php': ('../assets/css/theme.css', '../assets/js/theme-toggle.js'),
    'garbage/index.php': ('../assets/css/theme.css', '../assets/js/theme-toggle.js'),
    'road/index.php': ('../assets/css/theme.css', '../assets/js/theme-toggle.js'),
    'electricity/index.php': ('../assets/css/theme.css', '../assets/js/theme-toggle.js'),
    'super_admin/index.php': ('../assets/css/theme.css', '../assets/js/theme-toggle.js'),
    'admin/water/home.php': ('../../assets/css/theme.css', '../../assets/js/theme-toggle.js'),
    'admin/water/dashboard.php': ('../../assets/css/theme.css', '../../assets/js/theme-toggle.js'),
    'admin/water/manage_issues.php': ('../../assets/css/theme.css', '../../assets/js/theme-toggle.js'),
    'admin/water/update_status.php': ('../../assets/css/theme.css', '../../assets/js/theme-toggle.js'),
    'admin/garbage/home.php': ('../../assets/css/theme.css', '../../assets/js/theme-toggle.js'),
    'admin/garbage/dashboard.php': ('../../assets/css/theme.css', '../../assets/js/theme-toggle.js'),
    'admin/garbage/manage_issues.php': ('../../assets/css/theme.css', '../../assets/js/theme-toggle.js'),
    'admin/garbage/update_status.php': ('../../assets/css/theme.css', '../../assets/js/theme-toggle.js'),
    'admin/road/home.php': ('../../assets/css/theme.css', '../../assets/js/theme-toggle.js'),
    'admin/road/dashboard.php': ('../../assets/css/theme.css', '../../assets/js/theme-toggle.js'),
    'admin/road/manage_issues.php': ('../../assets/css/theme.css', '../../assets/js/theme-toggle.js'),
    'admin/road/update_status.php': ('../../assets/css/theme.css', '../../assets/js/theme-toggle.js'),
    'admin/electricity/home.php': ('../../assets/css/theme.css', '../../assets/js/theme-toggle.js'),
    'admin/electricity/dashboard.php': ('../../assets/css/theme.css', '../../assets/js/theme-toggle.js'),
    'admin/electricity/manage_issues.php': ('../../assets/css/theme.css', '../../assets/js/theme-toggle.js'),
    'admin/electricity/update_status.php': ('../../assets/css/theme.css', '../../assets/js/theme-toggle.js'),
    'admin/super_admin/home.php': ('../../assets/css/theme.css', '../../assets/js/theme-toggle.js'),
    'admin/super_admin/dashboard.php': ('../../assets/css/theme.css', '../../assets/js/theme-toggle.js'),
    'admin/super_admin/manage_all_issues.php': ('../../assets/css/theme.css', '../../assets/js/theme-toggle.js'),
    'admin/super_admin/manage_users.php': ('../../assets/css/theme.css', '../../assets/js/theme-toggle.js'),
}
for relative, (css_path, js_path) in patches.items():
    file_path = root / relative
    if not file_path.exists():
        print(f'MISSING: {relative}')
        continue
    text = file_path.read_text(encoding='utf-8')
    changed = False
    if css_path not in text:
        match = re.search(r'(<link[^>]+rel="stylesheet"[^>]*>)(\s*\n?)', text)
        if match:
            insert = f"{match.group(1)}\n    <link rel=\"stylesheet\" href=\"{css_path}\">{match.group(2)}"
            text = text[:match.start()] + insert + text[match.end():]
            changed = True
    if js_path not in text and '</body>' in text:
        text = text.replace('</body>', f'    <script src=\"{js_path}\"></script>\n</body>')
        changed = True
    if changed:
        file_path.write_text(text, encoding='utf-8')
        print(f'Patched: {relative}')
    else:
        print(f'No change: {relative}')
