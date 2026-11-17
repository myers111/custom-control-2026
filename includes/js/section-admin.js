jQuery(document).ready(function($) {
    var $pageMetaBox = $('#section_page_assignment');
    var $pageSelect  = $('select[name="assigned_page_id"]');
    var noticeId     = 'section-requires-page-or-parent';

    function togglePageAssignment(hasParent) {
        if (hasParent) {
            $pageSelect.val('');
            $pageMetaBox.slideUp(200);
        } else {
            $pageMetaBox.slideDown(200);
        }
    }

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
        validateClassicEditor();
        $classicParentSelect.on('change', validateClassicEditor);
        $pageSelect.on('change', validateClassicEditor);
    }

    // =========================================================
    // 2. BLOCK EDITOR (GUTENBERG) SUPPORT
    // =========================================================
    if (typeof wp !== 'undefined' && wp.data && wp.data.subscribe) {
        var lastParentId = null;
        var isCurrentlyValid = true;

        function setPublishButtonState(isValid) {
            // Strictly target ONLY the Publish / Update buttons, never Save Draft
            var $publishButtons = $('.editor-post-publish-panel__toggle, .editor-post-publish-button').not('.editor-post-save-draft');
            
            if (isValid) {
                $publishButtons.prop('disabled', false).css({
                    'pointer-events': '',
                    'opacity': ''
                });
                wp.data.dispatch('core/notices').removeNotice(noticeId);
            } else {
                $publishButtons.prop('disabled', true).css({
                    'pointer-events': 'none',
                    'opacity': '0.5'
                });
                wp.data.dispatch('core/notices').createNotice(
                    'warning',
                    'Select an Assigned Page or a Parent Section to publish. Save Draft remains available once you enter a title or content.',
                    {
                        id: noticeId,
                        isDismissible: false
                    }
                );
            }
        }

        function validateBlockEditor(currentParentId) {
            var hasParent = currentParentId > 0;
            isCurrentlyValid = hasValidAttachment(hasParent);
            setPublishButtonState(isCurrentlyValid);
        }

        wp.data.subscribe(function() {
            var editor = wp.data.select('core/editor');
            if (!editor) return;

            var currentParentId = editor.getEditedPostAttribute('parent');

            if (currentParentId !== undefined && currentParentId !== lastParentId) {
                lastParentId = currentParentId;
                togglePageAssignment(currentParentId > 0);
                validateBlockEditor(currentParentId);
            } else if (!isCurrentlyValid) {
                $('.editor-post-publish-panel__toggle, .editor-post-publish-button')
                    .not('.editor-post-save-draft')
                    .prop('disabled', true)
                    .css({
                        'pointer-events': 'none',
                        'opacity': '0.5'
                    });
            }
        });

        $pageSelect.on('change', function() {
            var editor = wp.data.select('core/editor');
            var currentParentId = editor ? editor.getEditedPostAttribute('parent') : 0;
            validateBlockEditor(currentParentId || 0);
        });
    }
});
