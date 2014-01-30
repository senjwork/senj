<div class="header">
    <div class="logoHeader">
        <a class="logo" href="<? if (I18n::lang() != 'ru'): ?>/<?= I18n::lang() ?><? endif; ?>/admin/"><?= __('Админка') ?></a>
        <a class="my_page" target="blank" href="/<?= Auth::instance()->get_user()->username ?><? if (I18n::lang() != 'ru'): ?>/<?= I18n::lang() ?><? endif; ?>"><?= Auth::instance()->get_user()->username ?></a>
        <div class="help">
            <a href="#"><?= __('Служба поддержки') ?></a>
            <a href="#"><?= __('Руководство пользователя') ?></a>
        </div>
    </div>
    <div class="topMenu">
        <ul>
            <li class="home"><a  href="<? if (I18n::lang() != 'ru'): ?>/<?= I18n::lang() ?><? endif; ?>/admin/"></a></li>
            <? if (Auth::instance()->logged_in('develop') != 0): ?>
                <li><a href="/admin/categories"><?= __('Категории') ?></a></li>
            <? endif; ?>

            <li class="out"><a href="/logout"></a></li>
            <li class="lang"><?= $view_langs ?></li>
            <li class="setup"><a href="<? if (I18n::lang() != 'ru'): ?>/<?= I18n::lang() ?><? endif; ?>/admin/setup"></a></li> 
            <!-- <li class="mail"><a href="#"><span>99</span></a></li> -->
            <? if (Auth::instance()->logged_in('admin')): ?>
                <li class="profil"><a href="<? if (I18n::lang() != 'ru'): ?>/<?= I18n::lang() ?><? endif; ?>/admin/users"></a></li>
            <? endif; ?>
            <li class="view"><a href="/" target="_blank"></a></li>

            <!--                                 <li class="search">
                                                    <form action="f" method="get">
                                                    <input name="" type="text" value="" placeholder="Поиск">
                                                    <input name="" type="submit" value="">
                                                    </form>
                                            </li> -->
        </ul>
    </div>
</div>