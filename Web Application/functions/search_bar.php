<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><?= isset($pageTitle) ? $pageTitle : 'Records'; ?></h4>
    <form method="get" action="" class="d-flex">
        <input 
            type="text" 
            name="search" 
            class="form-control search-bar me-2" 
            placeholder="<?= isset($searchBarPlaceholder) ? $placeholder : 'Search...'; ?>" 
            value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" 
        />
        <button class="btn btn-primary" type="submit">Search</button>
    </form>
</div>

<!-- CSS for Animations -->
<style>
    .search-bar {
        transition: width 0.3s ease;
        width: 200px;
    }

    .search-bar:focus {
        width: 600px;
    }
</style>