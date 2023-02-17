<div class="input-group mb-3">
    <span class="input-group-text">title</span>
    <input type="text" class="form-control seo_title" data-seo-item="1" placeholder="если не указано, то title = название товара (<?php echo $langs[0]['iso']; ?>)">
</div>

<div class="input-group mb-3">
    <span class="input-group-text">URL (ЧПУ)</span>
    <input type="text" class="form-control seo_slug" data-seo-item="1" placeholder="Человекопонятный URL">
</div>

<div class="form-floating">
    <textarea class="form-control seo_description" data-seo-item="1" id="seo_description" style="height: 160px" ></textarea>
    <label for="seo_description">Description (если не указано, то description = описание товара (<?php echo $langs[0]['iso']; ?>))</label>
</div>
