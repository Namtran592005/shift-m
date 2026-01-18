<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

requireAdmin($pdo);
$user = getCurrentUser($pdo);

/* Lay danh sach nhan vien */
$stmt = $pdo->prepare("SELECT * FROM nguoi_dung WHERE id != ? ORDER BY id DESC");
$stmt->execute([$user['id']]);
$employees = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<style>
    /* CSS bo sung */
    .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); z-index: 1000; justify-content: center; align-items: center; backdrop-filter: blur(4px); }
    .modal-box { background: white; width: 100%; max-width: 550px; border-radius: 12px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); display: flex; flex-direction: column; animation: slideIn 0.2s; }
    .modal-header { display: flex; justify-content: space-between; padding: 15px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; align-items: center; }
    .modal-body { padding: 20px; flex: 1; overflow-y: auto; }
    
    /* Style cho anh preview trong modal sua */
    .img-preview-box { width: 100%; height: 80px; object-fit: contain; border: 1px dashed #cbd5e1; border-radius: 6px; background: #f8fafc; display: block; margin-bottom: 5px; cursor: pointer; }
    
    /* Style cho anh trong modal Xem chi tiet */
    .view-card-img { width: 100%; height: auto; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    
    @keyframes slideIn { from { transform: translateY(10px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

    @media (max-width: 768px) {
        .flex-col-mobile { flex-direction: column; }
        .w-full-mobile { width: 100% !important; }
        .modal-box { height: 100%; max-height: 100%; border-radius: 0; }
    }
</style>

<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="flex justify-between items-center mb-20 flex-col-mobile">
                <h2 style="margin: 0;">Quản lý Nhân sự</h2>
                <div class="flex gap-10 flex-col-mobile w-full-mobile">
                    <a href="actions/export_employees.php" class="btn btn-outline w-full-mobile" style="color: #0ea5e9; border-color: #0ea5e9;">
                        <i class="fa-solid fa-file-export"></i> Xuất Excel
                    </a>
                    <button onclick="document.getElementById('importModal').style.display='flex'" class="btn btn-outline w-full-mobile" style="color: #15803d; border-color: #15803d;">
                        <i class="fa-solid fa-file-import"></i> Nhập Excel
                    </button>
                    <button onclick="openAddModal()" class="btn btn-primary w-full-mobile">
                        <i class="fa-solid fa-user-plus"></i> Thêm mới
                    </button>
                </div>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <div style="background: #ecfccb; color: #365314; padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #bef264; font-size: 13px;">
                    <i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($_GET['msg']); ?>
                </div>
            <?php endif; ?>

            <div class="card p-0" style="overflow: hidden;">
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; white-space: nowrap;">
                        <thead>
                            <tr>
                                <th style="padding: 15px;">Avatar</th>
                                <th style="padding: 15px;">Thông tin</th>
                                <th style="padding: 15px;">CCCD/CMND</th>
                                <th style="padding: 15px;">Mã thẻ NFC</th>
                                <th style="padding: 15px;">Vai trò</th>
                                <th style="padding: 15px;">Trạng thái</th>
                                <th style="padding: 15px;" class="text-right">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($employees as $emp): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 12px 15px;">
                                        <img src="<?php echo getAvatar($emp['avatar'], $emp['vai_tro']); ?>" 
                                             style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid #e2e8f0;">
                                    </td>
                                    <td style="padding: 12px 15px;">
                                        <div style="font-weight: 700; color: var(--primary); font-family: monospace; font-size: 13px;">
                                            <?php echo htmlspecialchars($emp['ma_nhan_vien'] ?? '---'); ?>
                                        </div>
                                        <div style="font-weight: 600; font-size: 14px;"><?php echo htmlspecialchars($emp['ho_ten']); ?></div>
                                        <div style="font-size: 12px; color: #64748b;"><?php echo htmlspecialchars($emp['email']); ?></div>
                                    </td>
                                    
                                    <!-- COT CCCD MOI -->
                                    <td style="padding: 12px 15px;">
                                        <?php if ($emp['so_cccd']): ?>
                                            <div style="font-weight: 600; font-size: 13px;"><?php echo htmlspecialchars($emp['so_cccd']); ?></div>
                                            
                                            <?php if ($emp['anh_cccd_truoc'] && $emp['anh_cccd_sau']): ?>
                                                <div style="font-size: 11px; margin-top: 2px; color: #16a34a;">
                                                    <i class="fa-solid fa-check-circle"></i> Đủ ảnh
                                                </div>
                                                <div style="margin-top: 3px;">
                                                    <a href="javascript:void(0)" 
                                                       onclick="openViewImagesModal('<?php echo $emp['anh_cccd_truoc']; ?>', '<?php echo $emp['anh_cccd_sau']; ?>', '<?php echo $emp['ho_ten']; ?>')"
                                                       style="font-size: 11px; color: var(--primary); text-decoration: underline;">
                                                       Xem chi tiết
                                                    </a>
                                                </div>
                                            <?php else: ?>
                                                <div style="font-size: 11px; margin-top: 2px; color: #ea580c;">
                                                    <i class="fa-solid fa-circle-exclamation"></i> Thiếu ảnh
                                                </div>
                                            <?php endif; ?>

                                        <?php else: ?>
                                            <span style="color: #cbd5e1;">---</span>
                                        <?php endif; ?>
                                    </td>

                                    <td style="padding: 12px 15px;">
                                        <?php if ($emp['nfc_uid']): ?>
                                            <span class="badge" style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;">
                                                <i class="fa-solid fa-id-card"></i> <?php echo htmlspecialchars($emp['nfc_uid']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="color: #cbd5e1; font-size: 12px; font-style: italic;">Chưa gán</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 12px 15px;">
                                        <span class="badge <?php echo $emp['vai_tro'] == 'admin' ? 'cho_duyet' : 'mo'; ?>">
                                            <?php echo $emp['vai_tro'] == 'admin' ? 'Quản trị' : 'Nhân viên'; ?>
                                        </span>
                                    </td>
                                    <td style="padding: 12px 15px;">
                                        <?php echo $emp['trang_thai'] == 1 ? '<span style="color: #22c55e; font-weight: 500;">● Hoạt động</span>' : '<span style="color: #ef4444; font-weight: 500;">● Đã khóa</span>'; ?>
                                    </td>
                                    <td style="padding: 12px 15px;" class="text-right">
                                        <button type="button" onclick='openEditModal(<?php echo htmlspecialchars(json_encode($emp), ENT_QUOTES, "UTF-8"); ?>)' 
                                                class="btn btn-outline btn-sm">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <a href="actions/admin_employee.php?action=delete&id=<?php echo $emp['id']; ?>" 
                                           onclick="return confirm('Bạn chắc chắn muốn xóa nhân viên này? Dữ liệu chấm công cũng sẽ bị xóa!')" 
                                           class="btn btn-outline btn-sm" style="color: #ef4444; border-color: #fecaca; margin-left: 5px;">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL 1: THEM MOI -->
    <div id="addModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3 style="margin:0; font-size: 18px;">Thêm nhân viên</h3>
                <i class="fa-solid fa-xmark" style="cursor:pointer; font-size: 20px; color: #94a3b8;" onclick="closeAllModals()"></i>
            </div>
            <form action="actions/admin_employee.php" method="POST" enctype="multipart/form-data" class="modal-body">
                <input type="hidden" name="action" value="add">
                
                <div style="background: #eff6ff; color: #1e40af; padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; border: 1px solid #dbeafe;">
                    <i class="fa-solid fa-circle-info"></i> Mã nhân viên sẽ được tạo tự động (VD: NV001).
                </div>

                <div class="input-group"><label>Họ tên *</label><input type="text" name="ho_ten" class="form-control" required></div>
                
                <div class="flex gap-10 flex-col-mobile">
                    <div class="input-group w-full"><label>Email *</label><input type="email" name="email" class="form-control" required></div>
                    <div class="input-group w-full"><label>Số CCCD</label><input type="text" name="so_cccd" class="form-control"></div>
                </div>
                
                <div style="background: #f8fafc; padding: 15px; border: 1px dashed #cbd5e1; border-radius: 8px; margin-bottom: 15px;">
                    <p style="font-size: 12px; font-weight: 700; color: #64748b; margin-top:0; margin-bottom: 10px;">HÌNH ẢNH GIẤY TỜ</p>
                    <div class="flex gap-10 flex-col-mobile">
                        <div style="flex:1"><label style="font-size: 11px;">Mặt trước</label><input type="file" name="cccd_truoc" class="form-control" style="font-size: 12px; padding-top: 8px;"></div>
                        <div style="flex:1"><label style="font-size: 11px;">Mặt sau</label><input type="file" name="cccd_sau" class="form-control" style="font-size: 12px; padding-top: 8px;"></div>
                    </div>
                </div>
                
                <div class="input-group mb-20">
                    <label>Vai trò</label>
                    <select name="vai_tro" class="form-control">
                        <option value="nhan_vien">Nhân viên</option>
                        <option value="admin">Quản trị viên</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-full">Tạo tài khoản</button>
            </form>
        </div>
    </div>

    <!-- MODAL 2: SUA -->
    <div id="editModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3 style="margin:0; font-size: 18px;">Cập nhật hồ sơ</h3>
                <i class="fa-solid fa-xmark" style="cursor:pointer; font-size: 20px; color: #94a3b8;" onclick="closeAllModals()"></i>
            </div>
            <form action="actions/admin_employee.php" method="POST" enctype="multipart/form-data" class="modal-body">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                
                <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 20px;">
                    <div style="text-align: center;">
                        <img id="edit_avatar_preview" src="" style="width: 70px; height: 70px; border-radius: 50%; object-fit: cover; border: 3px solid #f1f5f9;">
                        <label for="admin_upload_avt" style="display: block; font-size: 11px; color: var(--primary); cursor: pointer; margin-top: 5px;">Đổi ảnh</label>
                        <input type="file" name="avatar" id="admin_upload_avt" style="display: none;" onchange="previewEditImage(this, 'edit_avatar_preview')">
                    </div>
                    <div style="flex: 1;">
                        <div class="input-group" style="margin-bottom: 10px;"><label>Mã nhân viên</label><input type="text" id="edit_msnv" class="form-control" disabled style="background:#f1f5f9; font-weight:bold; height: 35px;"></div>
                        <div class="input-group" style="margin-bottom: 0;"><label>Họ tên</label><input type="text" name="ho_ten" id="edit_name" class="form-control" required style="height: 35px;"></div>
                    </div>
                </div>

                <div class="input-group"><label>Số CCCD</label><input type="text" name="so_cccd" id="edit_cccd" class="form-control"></div>

                <div style="background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #e2e8f0;">
                    <div class="flex gap-10 flex-col-mobile">
                        <div style="flex:1; text-align:center;">
                            <label style="font-size: 11px; display: block; text-align: left; margin-bottom: 5px;">Mặt trước</label>
                            <label for="up_cccd_truoc">
                                <img id="view_cccd_truoc" src="" class="img-preview-box">
                            </label>
                            <input type="file" id="up_cccd_truoc" name="cccd_truoc" style="display:none;" onchange="previewEditImage(this, 'view_cccd_truoc')">
                        </div>
                        <div style="flex:1; text-align:center;">
                            <label style="font-size: 11px; display: block; text-align: left; margin-bottom: 5px;">Mặt sau</label>
                            <label for="up_cccd_sau">
                                <img id="view_cccd_sau" src="" class="img-preview-box">
                            </label>
                            <input type="file" id="up_cccd_sau" name="cccd_sau" style="display:none;" onchange="previewEditImage(this, 'view_cccd_sau')">
                        </div>
                    </div>
                </div>

                <div class="input-group"><label>Mã thẻ NFC (UID)</label><input type="text" name="nfc_uid" id="edit_nfc" class="form-control" placeholder="Quét thẻ..." style="background: #fffbeb; border-color: #fde68a;"></div>
                <div class="input-group"><label>Email</label><input type="email" name="email" id="edit_email" class="form-control" required></div>
                <div class="input-group"><label>Mật khẩu mới</label><input type="password" name="mat_khau" class="form-control" placeholder="Để trống nếu không đổi"></div>

                <div class="flex gap-10 mb-20 flex-col-mobile">
                    <div class="input-group w-full"><label>Vai trò</label><select name="vai_tro" id="edit_role" class="form-control"><option value="nhan_vien">Nhân viên</option><option value="admin">Quản trị</option></select></div>
                    <div class="input-group w-full"><label>Trạng thái</label><select name="trang_thai" id="edit_status" class="form-control"><option value="1">Hoạt động</option><option value="0">Khóa</option></select></div>
                </div>
                
                <button type="submit" class="btn btn-primary w-full">Lưu thay đổi</button>
            </form>
        </div>
    </div>

    <!-- MODAL 3: IMPORT -->
    <div id="importModal" class="modal-overlay">
        <div class="modal-box" style="max-width: 400px; height: auto;">
            <div class="modal-header">
                <h3 style="margin:0;">Nhập từ Excel</h3>
                <i class="fa-solid fa-xmark" style="cursor:pointer; font-size: 20px;" onclick="document.getElementById('importModal').style.display='none'"></i>
            </div>
            <form action="actions/import_employees.php" method="POST" enctype="multipart/form-data" class="modal-body">
                <p style="font-size: 13px; color: #64748b; margin-bottom: 15px;">
                    1. Tải file mẫu: <a href="actions/download_template_xlsx.php" style="color: var(--primary); font-weight: bold; text-decoration: underline;">Mau_nhap.xlsx</a><br>
                    2. Điền thông tin nhân viên.<br>
                    3. Upload file lên hệ thống.
                </p>
                <input type="file" name="file" accept=".xlsx" class="form-control mb-20" style="padding-top: 8px;" required>
                <button type="submit" name="import" class="btn btn-primary w-full">Tiến hành Nhập</button>
            </form>
        </div>
    </div>

    <!-- MODAL 4: XEM ANH CCCD (UPDATE: DỌC + TẢI VỀ) -->
    <div id="viewImagesModal" class="modal-overlay">
        <div class="modal-box" style="max-width: 450px;"> <!-- Width nhỏ hơn -->
            <div class="modal-header">
                <h3 style="margin:0;">Giấy tờ: <span id="view_img_name" style="color: var(--primary);"></span></h3>
                <i class="fa-solid fa-xmark" style="cursor:pointer; font-size: 20px;" onclick="closeAllModals()"></i>
            </div>
            <div class="modal-body">
                <!-- XẾP DỌC (Không dùng flex ngang nữa) -->
                
                <!-- MẶT TRƯỚC -->
                <div style="margin-bottom: 25px; text-align: center;">
                    <div style="font-weight: 700; margin-bottom: 8px; color: #475569; font-size: 13px; text-transform: uppercase;">Mặt trước</div>
                    <img id="popup_img_truoc" src="" class="view-card-img" alt="Mat truoc" style="margin-bottom: 10px;">
                    <a id="dl_btn_truoc" href="#" download class="btn btn-outline btn-sm w-full" style="justify-content: center;">
                        <i class="fa-solid fa-download"></i> Tải ảnh về
                    </a>
                </div>

                <!-- MẶT SAU -->
                <div style="text-align: center;">
                    <div style="font-weight: 700; margin-bottom: 8px; color: #475569; font-size: 13px; text-transform: uppercase;">Mặt sau</div>
                    <img id="popup_img_sau" src="" class="view-card-img" alt="Mat sau" style="margin-bottom: 10px;">
                    <a id="dl_btn_sau" href="#" download class="btn btn-outline btn-sm w-full" style="justify-content: center;">
                        <i class="fa-solid fa-download"></i> Tải ảnh về
                    </a>
                </div>

            </div>
        </div>
    </div>

    <script>
        /* 1. MO MODAL THEM MOI */
        function openAddModal() {
            document.getElementById('addModal').style.display = 'flex';
        }

        /* 2. DONG TAT CA MODAL */
        function closeAllModals() {
            document.getElementById('addModal').style.display = 'none';
            document.getElementById('editModal').style.display = 'none';
            document.getElementById('importModal').style.display = 'none';
            document.getElementById('viewImagesModal').style.display = 'none';
            
            /* Reset anh */
            var p1 = document.getElementById('edit_avatar_preview');
            var p2 = document.getElementById('view_cccd_truoc');
            var p3 = document.getElementById('view_cccd_sau');
            if(p1) p1.src = '';
            if(p2) p2.src = '';
            if(p3) p3.src = '';
        }

        /* 3. MO MODAL SUA */
        function openEditModal(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_name').value = data.ho_ten;
            document.getElementById('edit_email').value = data.email;
            document.getElementById('edit_role').value = data.vai_tro;
            document.getElementById('edit_status').value = data.trang_thai;
            document.getElementById('edit_nfc').value = data.nfc_uid || '';
            document.getElementById('edit_cccd').value = data.so_cccd || '';
            document.getElementById('edit_msnv').value = data.ma_nhan_vien || '---';

            var avtSrc = data.avatar ? 'assets/uploads/' + data.avatar : (data.vai_tro === 'admin' ? 'assets/img/default_admin.png' : 'assets/img/default.png');
            document.getElementById('edit_avatar_preview').src = avtSrc;

            var srcTruoc = data.anh_cccd_truoc ? 'assets/uploads/' + data.anh_cccd_truoc : 'assets/img/placeholder_id.png';
            var imgTruoc = document.getElementById('view_cccd_truoc');
            if(imgTruoc) {
                imgTruoc.src = srcTruoc;
                imgTruoc.style.opacity = data.anh_cccd_truoc ? "1" : "0.5";
            }

            var srcSau = data.anh_cccd_sau ? 'assets/uploads/' + data.anh_cccd_sau : 'assets/img/placeholder_id.png';
            var imgSau = document.getElementById('view_cccd_sau');
            if(imgSau) {
                imgSau.src = srcSau;
                imgSau.style.opacity = data.anh_cccd_sau ? "1" : "0.5";
            }

            document.getElementById('editModal').style.display = 'flex';
        }

        /* 4. MO MODAL XEM ANH (CAP NHAT) */
        function openViewImagesModal(srcTruoc, srcSau, name) {
            document.getElementById('view_img_name').innerText = name;
            
            /* Set anh */
            document.getElementById('popup_img_truoc').src = 'assets/uploads/' + srcTruoc;
            document.getElementById('popup_img_sau').src = 'assets/uploads/' + srcSau;

            /* Set link tai ve */
            document.getElementById('dl_btn_truoc').href = 'assets/uploads/' + srcTruoc;
            document.getElementById('dl_btn_sau').href = 'assets/uploads/' + srcSau;

            document.getElementById('viewImagesModal').style.display = 'flex';
        }

        /* 5. PREVIEW ANH */
        function previewEditImage(input, targetId) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var img = document.getElementById(targetId);
                    if(img) {
                        img.src = e.target.result;
                        img.style.opacity = "1";
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        /* 6. CLICK RA NGOAI DE DONG */
        window.onclick = function(e) {
            if (e.target.classList.contains('modal-overlay')) closeAllModals();
        };
    </script>
</body>
<?php include 'includes/footer.php'; ?>