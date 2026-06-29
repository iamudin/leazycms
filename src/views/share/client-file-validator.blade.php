<script>
    document.addEventListener('DOMContentLoaded', function () {
        const compressImage = async (file, { quality = 0.8, maxWidth = 1700 } = {}) => {
            const imageBitmap = await createImageBitmap(file);
            let { width, height } = imageBitmap;
            if (width > maxWidth) {
                const scale = maxWidth / width;
                width = maxWidth;
                height = Math.round(height * scale);
            }
            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(imageBitmap, 0, 0, width, height);
            const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/webp', quality));
            const newFileName = file.name.replace(/\.[^/.]+$/, '') + '.webp';
            return new File([blob], newFileName, { type: 'image/webp' });
        };

        document.addEventListener('change', async function (e) {
            if (e.target && e.target.tagName === 'INPUT' && e.target.type === 'file') {
                const fileInput = e.target;
                const files = fileInput.files;

                // Clean up previous previews and errors
                const parent = fileInput.parentNode;
                const oldErrors = parent.querySelectorAll('.file-validation-error');
                oldErrors.forEach(el => el.remove());
                const oldPreviews = parent.querySelectorAll('.file-preview-image');
                oldPreviews.forEach(el => el.remove());
                fileInput.classList.remove('border-danger');
                fileInput.style.border = '';

                if (files.length === 0) return;

                const maxSizeAttr = fileInput.getAttribute('data-max-size');
                const maxSize = maxSizeAttr ? parseInt(maxSizeAttr, 10) : 5242880; // 5MB

                const rejectFile = (msg) => {
                    fileInput.value = '';
                    // Add red border
                    if (fileInput.classList.contains('form-control') || fileInput.classList.contains('form-control-file')) {
                        fileInput.classList.add('border-danger');
                    } else {
                        fileInput.style.border = '1px solid red';
                    }
                    // Insert error message below input
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'text-danger mt-1 file-validation-error';
                    errorDiv.style.fontSize = '0.875em';
                    errorDiv.style.color = 'red';
                    errorDiv.innerHTML = '<strong>Peringatan :</strong> ' + msg;
                    fileInput.insertAdjacentElement('afterend', errorDiv);
                };

                const dataTransfer = new DataTransfer();
                let hasChanges = false;
                let isValid = true;
                let previews = [];

                fileInput.disabled = true;
                const originalCursor = fileInput.style.cursor;
                fileInput.style.cursor = 'wait';

                for (let i = 0; i < files.length; i++) {
                    let file = files[i];

                    if (file.size > maxSize) {
                        rejectFile(`Ukuran file "${file.name}" terlalu besar. Maksimal ${(maxSize / 1024 / 1024).toFixed(2)} MB.`);
                        isValid = false;
                        break;
                    }

                    // Client-Side Extension and Mime Validation (Only for 'accept' attribute)
                    const fileName = file.name.toLowerCase();
                    const fileExt = fileName.split('.').pop();
                    const fileMime = file.type;
                    const acceptAttr = fileInput.getAttribute('accept');

                    if (acceptAttr) {
                        const acceptRules = acceptAttr.split(',').map(r => r.trim()).filter(r => r);
                        let isAccepted = false;
                        for (const rule of acceptRules) {
                            if (rule.startsWith('.')) {
                                if ('.' + fileExt === rule.toLowerCase()) {
                                    isAccepted = true;
                                    break;
                                }
                            } else if (rule.endsWith('/*')) {
                                const baseMime = rule.split('/')[0];
                                if (fileMime.startsWith(baseMime + '/')) {
                                    isAccepted = true;
                                    break;
                                }
                            } else {
                                if (rule === fileMime) {
                                    isAccepted = true;
                                    break;
                                }
                            }
                        }

                        if (!isAccepted) {
                            const basicMimeMap = {
                                'image/jpeg': '.jpg', 'image/png': '.png', 'image/gif': '.gif', 'image/webp': '.webp',
                                'application/pdf': '.pdf', 'application/msword': '.doc',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document': '.docx',
                                'application/vnd.ms-excel': '.xls',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': '.xlsx',
                                'video/mp4': '.mp4', 'audio/mpeg': '.mp3'
                            };
                            const displayExts = acceptRules.map(rule => {
                                if (rule.startsWith('.')) return rule.toLowerCase();
                                if (rule.endsWith('/*')) return `Semua file ${rule.split('/')[0]} (${rule})`;
                                return basicMimeMap[rule] || rule;
                            });
                            const uniqueExts = [...new Set(displayExts)].join(', ');
                            rejectFile(`Tipe file ditolak. Format yang diizinkan: ${uniqueExts}`);
                            isValid = false;
                            break;
                        }
                    }

                    if (file.type.startsWith('image/') && !file.type.includes('svg')) {
                        try {
                            file = await compressImage(file);
                            hasChanges = true;
                        } catch (err) {
                            console.error('Compression failed', err);
                        }
                    }

                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('accept', fileInput.getAttribute('accept') || '');
                    formData.append('_validate_file', '1');

                    try {
                        const response = await fetch("/", {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                            }
                        });

                        if (response.ok) {
                            const result = await response.json();
                            if (!result.success) {
                                rejectFile(`Validasi Gagal pada "${file.name}": ` + result.message);
                                isValid = false;
                                break;
                            }
                        } else {
                            console.error('Validation endpoint returned status: ' + response.status);
                        }
                    } catch (err) {
                        console.error('Validation AJAX error', err);
                    }

                    dataTransfer.items.add(file);

                    // Store file for preview if valid and is image
                    if (isValid && file.type.startsWith('image/')) {
                        previews.push(file);
                    }
                }

                fileInput.disabled = false;
                fileInput.style.cursor = originalCursor;

                if (!isValid) {
                    fileInput.value = '';
                } else {
                    if (hasChanges) {
                        fileInput.files = dataTransfer.files;
                    }
                    // Show previews
                    previews.forEach(previewFile => {
                        const img = document.createElement('img');
                        img.src = URL.createObjectURL(previewFile);
                        img.className = 'img-thumbnail mt-2 file-preview-image';
                        img.style.maxWidth = '150px';
                        img.style.display = 'block';
                        img.style.border = '1px solid #ddd';
                        img.style.padding = '4px';
                        img.style.borderRadius = '4px';
                        // Find the last element we inserted to append below it (if multiple files)
                        const lastPreview = parent.querySelector('.file-preview-image:last-of-type');
                        if (lastPreview) {
                            lastPreview.insertAdjacentElement('afterend', img);
                        } else {
                            fileInput.insertAdjacentElement('afterend', img);
                        }
                    });
                }
            }
        });
    });
</script>