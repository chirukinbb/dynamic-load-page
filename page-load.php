<?php
// Template Name: SPA

$wp_query = new WP_Query([
        'post_type' => 'post',
        'paged'     => get_query_var('page') ? get_query_var('page') : 1
]);

wp_head(); ?>

    <div class="container">

        <?php if (have_posts()): ?>

            <div class="page-1">

                <?php while (have_posts()):the_post(); ?>

                    <div class="card">
                        <img src="<?php echo esc_url_raw(get_the_post_thumbnail_url(get_the_ID())) ?>" style="height: 100px;overflow: hidden" alt="" class="img-fluid">
                        <a href="<?php the_permalink(); ?>">
                            <h2><?php the_title() ?></h2>
                        </a>
                        <?php the_excerpt(); ?>
                    </div>

                <?php endwhile; ?>

            </div>

            <?php
            $total_posts = wp_count_posts();
            $per_page = get_option('posts_per_page');
            $total_pages = ceil((int)$total_posts->publish/(int)$per_page);
            ?>

            <?php for ($i = 2; $i <= $total_pages; $i++): ?>

                <div class="page-<?php echo $i ?>" data-page="<?php echo $i ?>"></div>

            <?php endfor; ?>

            <template id="post">
                <div class="card">
                    <img src="" alt="" class="img-fluid">
                    <a href="">
                        <h2></h2>
                    </a>
                </div>
            </template>

        <?php endif; ?>

    </div>

    <script>
        (function ($) {
            $(this).scrollTop(0)

            var prevScroll = 0,
                page = 1,
                windowH = $(window).height(),
                positionY = $('.page-1').offset().top,
                blockH  = $('.page-1').height(),
                template = $('#post').html(),
                pagesCount = <?php echo $total_pages ?>,
                currentScroll,tmpl, onScroll

            $(window).on('scroll', onScroll = function () {
                currentScroll = $(this).scrollTop()

                if (currentScroll > prevScroll) {
                    prevScroll = currentScroll

                    if (0.8 * (blockH + positionY) < (windowH + currentScroll)) {
                        $(window).off('scroll')
                        page++

                        $.get(
                            '/wp-json/wp/v2/posts',
                            {
                                per_page: <?php echo $per_page ?>,
                                page: page,
                                _embed: 'wp:featuredmedia'
                            },
                            function (response) {
                                $(response).each(function (i,e) {
                                    tmpl = $(template).clone()
                                    $(tmpl).find('img').attr('src',e._embedded["wp:featuredmedia"][0].source_url)
                                    $(tmpl).find('a').attr('href', e.link)
                                    $(tmpl).find('h2').text(e.title.rendered)
                                    $(tmpl).append(e.excerpt.rendered)

                                    $('.page-' + page).append(tmpl)
                                })

                                $('.page-' + page).find('img').on('load', function (i,e) {

                                    positionY = $('.page-' + page).offset().top
                                    blockH  = $('.page-' + page).height()
                                })

                                if (page < pagesCount) {
                                    $(window).on('scroll', onScroll)
                                }
                            }
                        )
                    }
                }
            })
        }) (jQuery)
    </script>

<?php
wp_footer();