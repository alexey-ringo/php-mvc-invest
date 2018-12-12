<li>
    <?php if(!isset($category['childs']) ): ?>
        <a href="/category/<?=$category['id']; ?>"><?=$category['name']; ?></a>
    <?php else: ?>
        <a href="#"><?=$category['name']; ?></a>
    <?php endif ?>
    
        <?php if( isset($category['childs']) ): ?>
            <ul class="sub-menu">
                <?= $this->getMenuHtml($category['childs']) ?>
            </ul>
        <?php endif ?>
</li>
