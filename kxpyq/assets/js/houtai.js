/**
 * Pyq 主题后台设置 - Tab切换 + Checkbox美化
 */
document.addEventListener('DOMContentLoaded', function() {
    // Tab 切换
    var tabLinks = document.querySelectorAll('#theme-page .tabLinks');
    var tabContents = document.querySelectorAll('#theme-page .pyq-tab-content');

    tabLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            tabLinks.forEach(function(l) { l.classList.remove('active'); });
            tabContents.forEach(function(c) { c.classList.remove('active'); });
            this.classList.add('active');
            var target = document.getElementById(this.getAttribute('data-item'));
            if (target) target.classList.add('active');
        });
    });

    // Checkbox 美化
    var themePage = document.getElementById('theme-page');
    if (themePage) {
        var checkboxes = themePage.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(function(checkbox) {
            var toggle = document.createElement('span');
            toggle.className = 'imgcheck' + (checkbox.checked ? ' imgcheck-on' : '');
            checkbox.parentNode.insertBefore(toggle, checkbox.nextSibling);
            checkbox.style.display = 'none';

            toggle.addEventListener('click', function() {
                checkbox.checked = !checkbox.checked;
                toggle.classList.toggle('imgcheck-on', checkbox.checked);
            });

            checkbox.addEventListener('change', function() {
                toggle.classList.toggle('imgcheck-on', checkbox.checked);
            });
        });
    }

    // 色盘：为颜色输入框添加 color picker
    var colorFields = [];
    colorFields.forEach(function(name) {
        var input = themePage ? themePage.querySelector('input[name="'+name+'"]') : null;
        if (!input) return;
        var colorPicker = document.createElement('input');
        colorPicker.type = 'color';
        colorPicker.value = input.value || '#07c160';
        colorPicker.style.cssText = 'width:36px;height:32px;padding:2px;border:1px solid #e0e0e0;border-radius:6px;cursor:pointer;margin-left:8px;vertical-align:middle;';
        input.style.cssText += 'display:inline-block;width:calc(100% - 52px);vertical-align:middle;';
        input.parentNode.insertBefore(colorPicker, input.nextSibling);
        colorPicker.addEventListener('input', function() { input.value = this.value; });
        input.addEventListener('input', function() { if (/^#[0-9a-fA-F]{6}$/.test(this.value)) colorPicker.value = this.value; });
    });
});
