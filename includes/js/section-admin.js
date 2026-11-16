jQuery(document).ready(function($) {
    var $pageMetaBox = $('#section_page_assignment');
    var $pageSelect  = $('select[name="assigned_page_id"]');
    var lockKey      = 'section-requires-page-or-parent';

    // Helper: Toggle meta box visibility when a parent is selected
    function togglePageAssignment(hasParent) {
        if (hasParent) {
            $pageSelect.val('');
            $pageMetaBox.slideUp(200);
        } else {
            $pageMetaBox.slideDown(200);
        }
    }

    // Helper: Check if either Parent OR Assigned Page is selected
    function hasValidAttachment(hasParent) {
        var hasPage = $pageSelect.val() !== '' && $pageSelect.val() !== null;
        return hasParent || hasPage;
    }

    // =========================================================
    // 1. CLASSIC EDITOR SUPPORT
    // =========================================================
    var $classicParentSelect = $('#parent_id');
    var $classicPublishBtn   = $('#publish');

    function validateClassicEditor() {
        var hasParent = $classicParentSelect.val() !== '';
        togglePageAssignment(hasParent);

        if (hasValidAttachment(hasParent)) {
            $classicPublishBtn.prop('disabled', false).removeAttr('title');
        } else {
            $classicPublishBtn
                .prop('disabled', true)
                .attr('title', 'Select an Assigned Page or a Parent Section before publishing.');
        }
    }

    if ($classicParentSelect.length) {
        // Run immediately on page load
        validateClassicEditor();

        // Watch both dropdowns for changes
        $classicParentSelect.on('change', validateClassicEditor);
        $pageSelect.on('change', validateClassicEditor);
    }

    // =========================================================
    // 2. BLOCK EDITOR (GUTENBERG) SUPPORT
    // =========================================================
    if (typeof wp !== 'undefined' && wp.data && wp.data.subscribe) {
        var lastParentId = null;

        function validateBlockEditor(currentParentId) {
            var hasParent = currentParentId > 0;
            var isValid   = hasValidAttachment(hasParent);

            if (isValid) {
                wp.data.dispatch('core/editor').unlockPostSaving(lockKey);
                wp.data.dispatch('core/notices').removeNotice(lockKey);
            } else {
                wp.data.dispatch('core/editor').lockPostSaving(lockKey);
                wp.data.dispatch('core/notices').createNotice(
                    'warning',
                    'Please select an Assigned Page or a Parent Section to enable publishing.',
                    {
                        id: lockKey,
                        isDismissible: false
                    }
                );
            }
        }

        // Watch Gutenberg store for Parent changes
        wp.data.subscribe(function() {
            var editor = wp.data.select('core/editor');
            if (!editor) return;

            var currentParentId = editor.getEditedPostAttribute('parent');

            if (currentParentId !== undefined && currentParentId !== lastParentId) {
                lastParentId = currentParentId;
                togglePageAssignment(currentParentId > 0);
                validateBlockEditor(currentParentId);
            }
        });

        // Watch the custom Page dropdown for changes
        $pageSelect.on('change', function() {
            var editor = wp.data.select('core/editor');
            var currentParentId = editor ? editor.getEditedPostAttribute('parent') : 0;
            validateBlockEditor(currentParentId || 0);
        });
    }
});
