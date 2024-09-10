<nav class="navbar">
    <div class="nav-logo">
        archive*
    </div>
    <div class="nav-links">
        <ul>
            <li>home</li>
            <li>drawers</li>
            <li>about</li>
        </ul>
    </div>
    <div class="nav-menu">
        <ul class="d-flex align-items-center">
            @auth
                <li>
                    <a href="/posts/create">
                        <button style="background-color: var(--md-sys-color-primary)">
                            <i class="fa-solid fa-plus"></i>
                            <span>create</span>
                        </button>
                    </a>
                </li>
                <li>{{ Auth::user()->username }}</li>
                <li>
                    <form action="/logout" method="post">
                        @csrf
                        <input type="submit" value="logout"
                            style="background: none; border: none; color: var(--md-sys-color-on-surface)">
                    </form>
                </li>
            @endauth
            @guest
                <li><a href="/login">login</a></li>
            @endguest
        </ul>
    </div>
</nav>
