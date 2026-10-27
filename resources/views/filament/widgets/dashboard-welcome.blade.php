<x-filament-widgets::widget>
    <style>
        .djh-welcome {
            position: relative;
            isolation: isolate;
            overflow: hidden;
            padding: 28px 32px;
            border: 1px solid rgb(255 255 255 / 12%);
            border-radius: 20px;
            color: #fff;
            background: linear-gradient(120deg, #481015 0%, #771115 58%, #a53b1f 100%);
            box-shadow: 0 16px 36px rgb(79 17 21 / 18%);
        }

        .djh-welcome__decor {
            position: absolute;
            z-index: -1;
            pointer-events: none;
            border-radius: 9999px;
        }

        .djh-welcome__decor--ring {
            top: -92px;
            right: -52px;
            width: 250px;
            height: 250px;
            border: 32px solid rgb(255 255 255 / 7%);
        }

        .djh-welcome__decor--glow {
            right: 18%;
            bottom: -155px;
            width: 280px;
            height: 280px;
            background: rgb(251 191 36 / 16%);
            filter: blur(40px);
        }

        .djh-welcome__content {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 28px;
        }

        .djh-welcome__identity {
            max-width: 700px;
            min-width: 0;
        }

        .djh-welcome__eyebrow {
            display: flex;
            align-items: center;
            gap: 9px;
            color: #fde68a;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .djh-welcome__live {
            width: 8px;
            height: 8px;
            flex: 0 0 8px;
            border-radius: 50%;
            background: #4ade80;
            box-shadow: 0 0 0 4px rgb(74 222 128 / 18%);
        }

        .djh-welcome__title {
            margin: 14px 0 0;
            color: #fff;
            font-size: clamp(26px, 3vw, 38px);
            font-weight: 750;
            letter-spacing: -.025em;
            line-height: 1.2;
        }

        .djh-welcome__description {
            max-width: 620px;
            margin: 10px 0 0;
            color: rgb(255 255 255 / 82%);
            font-size: 15px;
            line-height: 1.65;
        }

        .djh-welcome__actions {
            display: flex;
            flex: 0 0 auto;
            flex-wrap: wrap;
            gap: 10px;
        }

        .djh-welcome__button {
            display: inline-flex;
            min-height: 44px;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 15px;
            border: 1px solid transparent;
            border-radius: 11px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            transition: background-color .15s ease, transform .15s ease;
        }

        .djh-welcome__button:hover {
            transform: translateY(-1px);
        }

        .djh-welcome__button--primary {
            color: #401014;
            background: #fcd34d;
        }

        .djh-welcome__button--primary:hover {
            color: #401014;
            background: #fde68a;
        }

        .djh-welcome__button--secondary {
            border-color: rgb(255 255 255 / 30%);
            color: #fff;
            background: rgb(255 255 255 / 10%);
        }

        .djh-welcome__button--secondary:hover {
            color: #fff;
            background: rgb(255 255 255 / 18%);
        }

        .djh-welcome__icon {
            width: 17px;
            height: 17px;
        }

        .djh-welcome__meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 24px;
            margin-top: 24px;
            padding-top: 14px;
            border-top: 1px solid rgb(255 255 255 / 18%);
            color: rgb(255 255 255 / 78%);
            font-size: 13px;
        }

        .djh-welcome__meta-item {
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .djh-welcome__meta-item .djh-welcome__icon {
            color: #fde68a;
        }

        @media (max-width: 760px) {
            .djh-welcome {
                padding: 23px 20px;
            }

            .djh-welcome__content {
                align-items: flex-start;
                flex-direction: column;
                gap: 20px;
            }

            .djh-welcome__actions {
                width: 100%;
            }

            .djh-welcome__button {
                flex: 1 1 auto;
            }
        }

        @media (max-width: 420px) {
            .djh-welcome__eyebrow {
                align-items: flex-start;
                font-size: 10px;
                letter-spacing: .1em;
            }

            .djh-welcome__meta {
                flex-direction: column;
                gap: 8px;
            }
        }
    </style>

    <section class="djh-welcome">
        <span class="djh-welcome__decor djh-welcome__decor--ring" aria-hidden="true"></span>
        <span class="djh-welcome__decor djh-welcome__decor--glow" aria-hidden="true"></span>

        <div class="djh-welcome__content">
            <div class="djh-welcome__identity">
                <div class="djh-welcome__eyebrow">
                    <span class="djh-welcome__live" aria-hidden="true"></span>
                    <span>{{ $brandName }} <span aria-hidden="true">/</span> Admin workspace</span>
                </div>
                <h1 class="djh-welcome__title">Welcome back, {{ $user?->name ?? 'Admin' }}</h1>
                <p class="djh-welcome__description">
                    Here’s what’s happening with your store today. Keep orders moving and your campus collection in stock.
                </p>
            </div>

            <nav class="djh-welcome__actions" aria-label="Dashboard shortcuts">
                <a href="{{ url('/admin/orders') }}" class="djh-welcome__button djh-welcome__button--primary">
                    <x-filament::icon icon="heroicon-o-shopping-bag" class="djh-welcome__icon" />
                    Manage orders
                </a>
                <a href="{{ url('/admin/products') }}" class="djh-welcome__button djh-welcome__button--secondary">
                    <x-filament::icon icon="heroicon-o-rectangle-stack" class="djh-welcome__icon" />
                    View catalog
                </a>
            </nav>
        </div>

        <div class="djh-welcome__meta">
            <span class="djh-welcome__meta-item">
                <x-filament::icon icon="heroicon-o-calendar-days" class="djh-welcome__icon" />
                {{ now()->format('l, F j, Y') }}
            </span>
            <span class="djh-welcome__meta-item">
                <x-filament::icon icon="heroicon-o-clock" class="djh-welcome__icon" />
                {{ now()->timezone(config('app.timezone'))->format('g:i A') }} ({{ config('app.timezone') }})
            </span>
        </div>
    </section>
</x-filament-widgets::widget>
