<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../functions/auth_check.php');
include('../includes/header.php');
require_once('../actions/fetch_maps_process.php');
require_once('../actions/fetch_worksites.php');

// 获取地图 ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $map = getMapById($id); // 从数据库中获取地图信息
    if ($map) {
        // 地图存在，继续显示表单
    } else {
        $_SESSION['status'] = "Map not found.";
        header("Location: map-list.php");
        exit();
    }
} else {
    $_SESSION['status'] = "Invalid request.";
    header("Location: map-list.php");
    exit();
}
?>

<div class="container">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card mt-4">
                <div class="card-header">
                    <h4>Edit Escape Route Map</h4>
                </div>
                <div class="card-body">
                    <!-- 表单开始 -->
                    <form action="../actions/update_map_process.php" method="POST" enctype="multipart/form-data">
                        <!-- 隐藏字段：地图 ID -->
                        <input type="hidden" name="map_id" value="<?= htmlspecialchars($map['id']); ?>">

                        <!-- 上传图片 -->
                        <div class="form-group mb-4">
                            <label for="map_image" class="form-label">Change Map Image (Optional)</label>
                            <input type="file" name="map_image" id="map_image" class="form-control" accept="image/*">
                            <small class="form-text text-muted">Leave blank to keep the current image.</small>
                            <div class="mt-2">
                                <strong>Current Image:</strong>
                                <p><?= htmlspecialchars($map['image_path']); ?></p>
                            </div>
                        </div>

                        <!-- 选择工作地点 -->
                        <div class="form-group mb-4">
                            <label for="worksite_id" class="form-label">Select Worksite</label>
                            <select name="worksite_id" id="worksite_id" class="form-select" required>
                                <?php
                                $worksites = getWorksiteNameANDID();
                                if (!empty($worksites)) {
                                    foreach ($worksites as $worksite) {
                                        $selected = $worksite['worksite_id'] == $map['worksite_id'] ? 'selected' : '';
                                        echo "<option value='" . htmlspecialchars($worksite['worksite_id']) . "' $selected>" . htmlspecialchars($worksite['worksite_name']) . "</option>";
                                    }
                                } else {
                                    echo "<option value='' disabled>No worksites available</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- 编辑问题 -->
                        <div class="form-group mb-4">
                            <label for="question" class="form-label">Edit Question</label>
                            <textarea name="question" id="question" class="form-control" rows="3" required><?= htmlspecialchars($map['question']); ?></textarea>
                        </div>

                        <!-- 编辑选项 -->
                        <div class="form-group mb-3">
                            <label for="option_a">Option A</label>
                            <input type="text" name="option_a" id="option_a" class="form-control" value="<?= htmlspecialchars($map['option_a']); ?>" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="option_b">Option B</label>
                            <input type="text" name="option_b" id="option_b" class="form-control" value="<?= htmlspecialchars($map['option_b']); ?>" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="option_c">Option C</label>
                            <input type="text" name="option_c" id="option_c" class="form-control" value="<?= htmlspecialchars($map['option_c']); ?>" required>
                        </div>

                        <!-- 正确答案 -->
                        <div class="form-group mb-3">
                            <label for="correct_answer">Correct Answer</label>
                            <select name="correct_answer" id="correct_answer" class="form-control" required>
                                <option value="option_a" <?= $map['correct_answer'] == 'option_a' ? 'selected' : ''; ?>>Option A</option>
                                <option value="option_b" <?= $map['correct_answer'] == 'option_b' ? 'selected' : ''; ?>>Option B</option>
                                <option value="option_c" <?= $map['correct_answer'] == 'option_c' ? 'selected' : ''; ?>>Option C</option>
                            </select>
                        </div>

                        <!-- 提交按钮 -->
                        <div class="form-group">
                            <button type="submit" name="update_map" class="btn btn-primary w-100">Update Map</button>
                        </div>
                    </form>
                    <!-- 表单结束 -->
                </div>
            </div>
        </div>
    </div>
</div>
<?php include('../includes/footer.php'); ?>