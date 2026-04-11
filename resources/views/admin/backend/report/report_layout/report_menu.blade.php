<ul class="navbar-nav">
        <li class="nav-item">
            <a href="{{ route('report.index') }}" class="nav-link active" aria-current="page">Purchase</a>
        </li>
        <li class="nav-item">
            <a href="{{ route('report.purchase-return') }}" class="nav-link purchase-return-tab" >Purchase Return</a>
        </li>

        <li class="nav-item">
            <a href="{{ route('report.sale') }}" class="nav-link" >Sale</a>
        </li>
        <li class="nav-item">
            <a href="{{ route('report.sale-return') }}" class="nav-link" >Sale Return</a>
        </li>

        <li class="nav-item">
            <a href="{{ route('report.stock') }}" class="nav-link" >Stock</a>
        </li>

    </ul>
