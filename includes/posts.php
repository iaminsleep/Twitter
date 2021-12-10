<?php if($posts) {?>
<section class="wrapper">
    <ul class="tweet-list">
        <?php foreach($posts as $post) { ?>
        <li>
            <article class="tweet">
                <div class="row">
                    <img class="avatar" src="<?php echo get_url($post['avatar']);?>" alt="Аватар пользователя <?php echo $post['name'];?>">
                    <div class="tweet__wrapper">
                        <header class="tweet__header">
                            <h3 class="tweet-author"><?php echo $post['name'];?>
                                <a href="<?php echo get_url('user_posts.php?id='.$post['user_id']);?>" class="tweet-author__add tweet-author__nickname">
                                    @<?php echo $post['login'];?>
                                </a>
                                <time class="tweet-author__add tweet__date"><?php echo date('d.m.y в H:i', strtotime($post['date']));?></time>
                            </h3>
                            <?php if ($isAuth && $post['user_id'] == $_SESSION['user']['id']): ?>
                            <a href="<?php echo get_url('includes/delete_post.php?id='.$post['id'])?>" class="tweet__delete-button chest-icon"></a>
                            <?php endif;?>
                        </header>
                        <div class="tweet-post">
                            <p class="tweet-post__text"><?php echo $post['text'];?></p>
                            <?php if ($post['image']) { ?>
                            <figure class="tweet-post__image">
                                <img src="<?php echo $post['image'];?>" alt="Tweet Image">
                            </figure>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <footer class="tweet__footer">
                    <?php if ($isAuth) {?>
                        <?php
                        $is_liked = is_post_liked($post['id']);
                        $likeStatus = $is_liked ? 'delete_like.php?id=' : 'add_like.php?id=';
                        $likes_count = get_likes_count($post['id']);
                        ?>
                        <a href="<?php echo get_url("includes/$likeStatus".$post['id'])?>"
                           class="tweet__like <?php echo $is_liked ? 'tweet__like_active' : ''?>">
                            <?php echo $likes_count?></a>
                    <?php }?>
                </footer>
            </article>
        </li>
        <?php } ?>
    </ul>
</section>
<?php } else {
    echo '<h3 style="display: flex; justify-content: center; margin: 50px;">Здесь пока что пусто...</h3>';
}
?>