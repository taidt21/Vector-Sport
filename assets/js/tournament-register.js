/**
 * Thêm/bớt dòng VĐV trong form "Thêm VĐV" ở trang Quản lý đội — nhân bản
 * nội dung <template id="tournament-athlete-row-template">, thay
 * __INDEX__ bằng số thứ tự tăng dần để mỗi dòng có tên field riêng
 * (athlete[N][...]). Đồng thời hỗ trợ preview avatar và hiện tên file Excel.
 */
(function ($) {
    'use strict';

    $(function () {
        var $rowsContainer = $('#tournament-athlete-rows');
        var $template = $('#tournament-athlete-row-template');

        function updateExcelFileName(input) {
            var fileName = (input.files && input.files[0] && input.files[0].name)
                ? input.files[0].name
                : 'Chưa có tệp nào được chọn';
            $('[data-excel-file-name]').first().text(fileName);
        }

        function renumberRows() {
            $rowsContainer.find('[data-athlete-row]').each(function (i) {
                $(this).find('[data-athlete-no]').first().text(i + 1);
            });
        }

        function updateRemoveButtons() {
            var $rows = $rowsContainer.find('[data-athlete-row]');
            $rows.find('[data-remove-athlete]').prop('disabled', $rows.length <= 1);
        }

        function filterEventsForRow($row) {
            var selected = $row.find('[data-nhom-tuoi-select]').val();
            var $box = $row.find('[data-nt-box]');

            $box.find('[data-nt-group]').each(function () {
                var group = $(this).attr('data-nt-group');
                var show = group === 'hon_hop' || (selected && group === selected);

                $(this).toggle(Boolean(show));

                if (!show) {
                    $(this).find('input[type="checkbox"]').prop('checked', false);
                }
            });

            $row.find('[data-nt-hint]').toggle(!selected);
        }

        function previewAvatar(input) {
            var file = input.files && input.files[0];
            var $preview = $(input).closest('.vs-athlete-avatar-box').find('[data-avatar-preview]').first();

            if (!file || !$preview.length) {
                return;
            }

            if (!file.type.match(/^image\//)) {
                return;
            }

            var reader = new FileReader();
            reader.onload = function (e) {
                $preview.html(
                    $('<img>', {
                        src: e.target.result,
                        alt: '',
                        class: 'vs-athlete-avatar-box__img'
                    })
                );
            };
            reader.readAsDataURL(file);
        }

        $(document).on('change', '[data-excel-file-input]', function () {
            updateExcelFileName(this);
        });

        if ($rowsContainer.length && $template.length) {
            var nextIndex = 1;

            $rowsContainer.on('change', '[data-nhom-tuoi-select]', function () {
                filterEventsForRow($(this).closest('[data-athlete-row]'));
            });

            $('#tournament-add-athlete').on('click', function () {
                var html = $template.prop('content')
                    ? $($template.prop('content')).clone()
                    : $($.parseHTML($template.html()));

                var $html = $('<div>').append(html);
                var rowHtml = $html.html().split('__INDEX__').join(String(nextIndex));

                $rowsContainer.append(rowHtml);
                nextIndex++;

                renumberRows();
                updateRemoveButtons();
                filterEventsForRow($rowsContainer.find('[data-athlete-row]').last());
            });

            $rowsContainer.on('click', '[data-remove-athlete]', function () {
                var $rows = $rowsContainer.find('[data-athlete-row]');

                if ($rows.length <= 1) {
                    return;
                }

                $(this).closest('[data-athlete-row]').remove();
                renumberRows();
                updateRemoveButtons();
            });

            $rowsContainer.on('change', '[data-avatar-input]', function () {
                previewAvatar(this);
            });

            updateRemoveButtons();
            $rowsContainer.find('[data-athlete-row]').each(function () {
                filterEventsForRow($(this));
            });
        }
    });

    /**
     * Tích chọn hàng loạt VĐV để xoá — checkbox từng hàng KHÔNG nằm
     * trong <form> nào (tránh lồng form vào form upload ảnh/sửa/xoá
     * từng hàng, HTML không cho phép) — dùng thuộc tính form="..." trên
     * nút "Xoá đã chọn" để trỏ tới #vs-bulk-delete-form nằm riêng, rồi
     * JS tự điền athlete_ids[] vào đúng lúc form đó chuẩn bị gửi.
     */
    $(function () {
        var $selectAll = $('#vs-select-all-athletes');
        var $checkboxes = $('.vs-athlete-checkbox');
        var $bulkDeleteBtn = $('#vs-bulk-delete-btn');
        var $bulkDeleteForm = $('#vs-bulk-delete-form');
        var $countLabel = $('#vs-selected-count');

        if (!$checkboxes.length || !$bulkDeleteForm.length) {
            return;
        }

        function updateBulkDeleteState() {
            var checkedCount = $('.vs-athlete-checkbox:checked').length;
            $countLabel.text(checkedCount);
            $bulkDeleteBtn.prop('disabled', checkedCount === 0);
            $selectAll.prop('checked', checkedCount > 0 && checkedCount === $checkboxes.length);
        }

        $selectAll.on('change', function () {
            $checkboxes.prop('checked', $(this).is(':checked'));
            updateBulkDeleteState();
        });

        $checkboxes.on('change', updateBulkDeleteState);

        $bulkDeleteForm.on('submit', function () {
            var $form = $(this);
            $form.find('input[name="athlete_ids[]"]').remove();

            $('.vs-athlete-checkbox:checked').each(function () {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'athlete_ids[]',
                    value: $(this).val()
                }).appendTo($form);
            });
        });

        updateBulkDeleteState();
    });

    /**
     * Đổi ảnh nhanh trên từng hàng bằng AJAX — không load lại trang. Vẫn
     * giữ nguyên <form> POST thường bao quanh (nếu JS lỗi/tắt, form vẫn
     * gửi được theo cách cũ, chỉ là load lại trang — không mất tính
     * năng hoàn toàn). Dùng event delegation trên document vì có nhiều
     * ô ảnh (mỗi VĐV 1 ô), không cần lặp gắn từng cái.
     */
    $(function () {
        if (typeof vsTeamPortal === 'undefined' || !vsTeamPortal.ajaxUrl) {
            return;
        }

        $(document).on('change', '.team_portal_page__avatar_upload_input', function () {
            var $input = $(this);
            var file = this.files && this.files[0];

            if (!file) {
                return;
            }

            var $form = $input.closest('form');
            var $label = $input.closest('.team_portal_page__avatar_upload_label');

            var formData = new FormData();
            formData.append('action', 'vs_update_athlete_avatar');
            formData.append('nonce', $form.find('input[name="vs_team_portal_nonce"]').val());
            formData.append('athlete_id', $form.find('input[name="athlete_id"]').val());
            formData.append('anh_dai_dien', file);

            $label.css('opacity', 0.4);

            $.ajax({
                url: vsTeamPortal.ajaxUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false
            }).done(function (response) {
                if (response && response.success && response.data && response.data.avatar_url) {
                    var $existingImg = $label.find('img');

                    if ($existingImg.length) {
                        $existingImg.attr('src', response.data.avatar_url);
                    } else {
                        $label.find('.team_portal_page__avatar_placeholder').replaceWith(
                            $('<img>')
                                .addClass('team_portal_page__avatar_thumb')
                                .attr({ src: response.data.avatar_url, alt: '' })
                        );
                    }
                } else {
                    var message = (response && response.data && response.data.message)
                        || 'Có lỗi khi tải ảnh lên, vui lòng thử lại.';
                    window.alert(message);
                }
            }).fail(function () {
                window.alert('Có lỗi khi tải ảnh lên, vui lòng thử lại.');
            }).always(function () {
                $label.css('opacity', '');
                $input.val('');
            });
        });
    });


    /**
     * Thêm/bớt nhiều thành viên Ban huấn luyện trong cùng một form.
     * Chỉ cho phép chọn Trưởng đoàn ở tối đa 1 dòng; backend vẫn kiểm tra lại.
     */
    $(function () {
        var $staffRows = $('#tournament-staff-rows');
        var $staffTemplate = $('#tournament-staff-row-template');

        if (!$staffRows.length || !$staffTemplate.length) {
            return;
        }

        var nextStaffIndex = 1;
        var hasExistingLeader = $('[data-existing-team-leader="1"]').length > 0;

        function renumberStaffRows() {
            $staffRows.find('[data-staff-row]').each(function (i) {
                $(this).find('[data-staff-no]').first().text(i + 1);
            });
        }

        function updateStaffRemoveButtons() {
            var $rows = $staffRows.find('[data-staff-row]');
            $rows.find('[data-remove-staff]').prop('disabled', $rows.length <= 1);
        }

        function syncLeaderOptions() {
            var leaderSelected = false;

            $staffRows.find('[data-staff-role-select]').each(function () {
                if ($(this).val() === 'truong_doan') {
                    leaderSelected = true;
                }
            });

            $staffRows.find('[data-staff-role-select]').each(function () {
                var $select = $(this);
                var isCurrentLeader = $select.val() === 'truong_doan';
                var disableLeader = hasExistingLeader || (leaderSelected && !isCurrentLeader);

                $select.find('option[value="truong_doan"]').prop('disabled', disableLeader);
            });
        }

        function previewStaffAvatar(input) {
            var file = input.files && input.files[0];
            var $preview = $(input)
                .closest('.vs-staff-avatar-box')
                .find('[data-bulk-staff-avatar-preview]')
                .first();

            if (!file || !$preview.length || !file.type.match(/^image\//)) {
                return;
            }

            var reader = new FileReader();
            reader.onload = function (event) {
                $preview.html(
                    $('<img>', {
                        src: event.target.result,
                        alt: '',
                        class: 'vs-staff-avatar-box__img'
                    })
                );
            };
            reader.readAsDataURL(file);
        }

        $('#tournament-add-staff').on('click', function () {
            var html = $staffTemplate.prop('content')
                ? $($staffTemplate.prop('content')).clone()
                : $($.parseHTML($staffTemplate.html()));

            var $html = $('<div>').append(html);
            var rowHtml = $html.html().split('__INDEX__').join(String(nextStaffIndex));

            $staffRows.append(rowHtml);
            nextStaffIndex++;

            renumberStaffRows();
            updateStaffRemoveButtons();
            syncLeaderOptions();
        });

        $staffRows.on('click', '[data-remove-staff]', function () {
            var $rows = $staffRows.find('[data-staff-row]');
            if ($rows.length <= 1) {
                return;
            }

            $(this).closest('[data-staff-row]').remove();
            renumberStaffRows();
            updateStaffRemoveButtons();
            syncLeaderOptions();
        });

        $staffRows.on('change', '[data-staff-role-select]', syncLeaderOptions);
        $staffRows.on('change', '[data-bulk-staff-avatar-input]', function () {
            previewStaffAvatar(this);
        });

        updateStaffRemoveButtons();
        syncLeaderOptions();
    });

})(jQuery);
