<div class="site-sidebar">
    <div class="custom-scroll custom-scroll-light">
        <ul class="sidebar-menu">
            <!-- DASHBOARD -->
            <li class="menu-title">PicMe225</li>
            <li>
                <a href="{{ route('admin.dashboard') }}" class="waves-effect waves-light">
                    <span class="s-icon"><i class="ti-anchor"></i></span>
                    <span class="s-text">@lang('admin.include.dashboard')</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.dispatcher.index') }}" class="waves-effect waves-light">
                    <span class="s-icon"><i class="ti-target"></i></span>
                    <span class="s-text">@lang('admin.include.dispatcher_panel')</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.heatmap') }}" class="waves-effect waves-light">
                    <span class="s-icon"><i class="ti-map"></i></span>
                    <span class="s-text">@lang('admin.include.heat_map')</span>
                </a>
            </li>

            <!-- FINANCE & COMPTABILITÉ -->
            <li class="menu-title">Finance & Comptabilité</li>
            <li class="with-sub">
                <a href="#" class="waves-effect waves-light">
                    <span class="s-caret"><i class="fa fa-angle-down"></i></span>
                    <span class="s-icon"><i class="ti-wallet"></i></span>
                    <span class="s-text">Trésorerie</span>
                </a>
                <ul>
                    <li><a href="{{ url('admin/treasury') }}">Trésorerie & Liquidité</a></li>
                    <li><a href="{{ route('admin.payment') }}">@lang('admin.include.payment_history')</a></li>
                    <li><a href="{{ route('admin.settings.payment') }}">@lang('admin.include.payment_settings')</a></li>
                </ul>
            </li>
            <li class="with-sub">
                <a href="#" class="waves-effect waves-light">
                    <span class="s-caret"><i class="fa fa-angle-down"></i></span>
                    <span class="s-icon"><i class="ti-receipt"></i></span>
                    <span class="s-text">Comptabilité & TVA</span>
                </a>
                <ul>
                    <li><a href="{{ url('admin/tva-accounting') }}">Comptabilité TVA</a></li>
                    <li><a href="{{ route('admin.ride.statement') }}">@lang('admin.include.overall_ride_statments')</a>
                    </li>
                    <li><a
                            href="{{ route('admin.ride.statement.provider') }}">@lang('admin.include.provider_statement')</a>
                    </li>
                    <li><a href="{{ route('admin.ride.statement.today') }}">@lang('admin.include.daily_statement')</a>
                    </li>
                    <li><a
                            href="{{ route('admin.ride.statement.monthly') }}">@lang('admin.include.monthly_statement')</a>
                    </li>
                    <li><a href="{{ route('admin.ride.statement.yearly') }}">@lang('admin.include.yearly_statement')</a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="{{ url('admin/dao-finance') }}" class="waves-effect waves-light">
                    <span class="s-icon"><i class="ti-pie-chart"></i></span>
                    <span class="s-text">Finances DAO</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.gateway.index') }}" class="waves-effect waves-light">
                    <span class="s-icon" style="color: #f59345;"><i class="ti-mobile"></i></span>
                    <span class="s-text">Gateway Hub (P2P)</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.sms-booking.index') }}" class="waves-effect waves-light">
                    <span class="s-icon text-info"><i class="ti-comment-alt"></i></span>
                    <span class="s-text">Logs SMS Booking</span>
                </a>
            </li>

            <!-- SERVICES & VARIANTES -->
            <li class="menu-title">Services & Variantes</li>
            <li class="with-sub">
                <a href="#" class="waves-effect waves-light">
                    <span class="s-caret"><i class="fa fa-angle-down"></i></span>
                    <span class="s-icon"><i class="ti-view-grid"></i></span>
                    <span class="s-text">@lang('admin.include.service_types')</span>
                </a>
                <ul>
                    <li style="background: #f1f5f9; padding: 5px; font-size: 0.8rem; font-weight: bold; margin-left:15px; color: #64748b;">Grandes Catégories</li>
                    <li><a href="{{ route('admin.main-category.index') }}">Liste des Catégories</a></li>
                    <li><a href="{{ route('admin.main-category.create') }}">Nouvelle Catégorie</a></li>
                    <li style="background: #f1f5f9; padding: 5px; font-size: 0.8rem; font-weight: bold; margin-left:15px; color: #64748b;">Sous-Services</li>
                    <li><a href="{{ route('admin.service.index') }}">@lang('admin.include.list_service_types')</a></li>
                    <li><a href="{{ route('admin.service.create') }}">@lang('admin.include.add_new_service_type')</a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="{{ route('admin.settings.variants') }}" class="waves-effect waves-light">
                    <span class="s-icon"><i class="ti-split-v"></i></span>
                    <span class="s-text">Ride Variants & DAO</span>
                </a>
            </li>
            <li>
                <a href="{{route('admin.kmhour.index') }}" class="waves-effect waves-light">
                    <span class="s-icon"><i class="ti-time"></i></span>
                    <span class="s-text">@lang('admin.include.km-hours') (Forfaits)</span>
                </a>
            </li>

            <!-- LOCATION DE VÉHICULES -->
            <li class="menu-title">Location de Véhicules</li>
            <li class="with-sub">
                <a href="#" class="waves-effect waves-light">
                    <span class="s-caret"><i class="fa fa-angle-down"></i></span>
                    <span class="s-icon"><i class="ti-car"></i></span>
                    <span class="s-text">Location sans Chauffeur</span>
                </a>
                <ul>
                    <li><a href="{{ route('admin.location.index') }}">Véhicules en Location</a></li>
                    <li><a href="{{ route('admin.location.create') }}">Ajouter un Véhicule</a></li>
                    <li><a href="{{ route('admin.location.bookings') }}">Demandes de Réservation</a></li>
                </ul>
            </li>

            <!-- BILLETTERIE & ÉVÉNEMENTS -->
            <li class="menu-title">Billetterie & Événements</li>
            <li class="with-sub">
                <a href="#" class="waves-effect waves-light">
                    <span class="s-caret"><i class="fa fa-angle-down"></i></span>
                    <span class="s-icon"><i class="ti-ticket"></i></span>
                    <span class="s-text">Tickets & Guichet</span>
                </a>
                <ul>
                    <li><a href="{{ route('admin.tickets.sell.create') }}">Guichet (Vendre)</a></li>
                    <li><a href="{{ route('admin.tickets.sold') }}">Billets Vendus</a></li>
                </ul>
            </li>

            <!-- MARKETPLACE & ACTUALITÉS -->
            <li class="menu-title">Marketplace & Actualités</li>
            <li class="with-sub">
                <a href="#" class="waves-effect waves-light">
                    <span class="s-caret"><i class="fa fa-angle-down"></i></span>
                    <span class="s-icon"><i class="ti-shopping-cart-full"></i></span>
                    <span class="s-text">Marketplace</span>
                </a>
                <ul>
                    <li><a href="{{ route('admin.marketplace-listings.index') }}">Toutes les annonces</a></li>
                    <li><a href="{{ route('admin.marketplace-listings.create') }}">Publier une annonce</a></li>
                    <li><a href="{{ route('admin.marketplace-categories.index') }}">Gérer les Catégories</a></li>
                </ul>
            </li>

            <!-- MARKETPLACE & WHATSAPP IA -->
            <li class="menu-title" style="background: #e8f5e9; color: #155724;"><i class="fa fa-whatsapp"></i> Marketplace & WhatsApp IA</li>
            <li class="with-sub">
                <a href="#" class="waves-effect waves-light" style="color: #25D366; font-weight: 700;">
                    <span class="s-caret"><i class="fa fa-angle-down"></i></span>
                    <span class="s-icon"><i class="fa fa-whatsapp"></i></span>
                    <span class="s-text">WhatsApp & Intégrations</span>
                </a>
                <ul>
                    <li>
                        <a href="{{ route('admin.whatsapp.connect') }}">
                            <span class="s-icon"><i class="fa fa-qrcode" style="color: #25D366;"></i></span>
                            <span class="s-text">Connexion WhatsApp</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.whatsapp.index') }}">
                            <span class="s-icon"><i class="fa fa-whatsapp" style="color: #25D366;"></i></span>
                            <span class="s-text">Annonces IA WhatsApp</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.whatsapp.broadcast.index') }}">
                            <span class="s-icon"><i class="fa fa-bullhorn" style="color: #25D366;"></i></span>
                            <span class="s-text">WhatsApp Broadcast (IA)</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.whatsapp-groups.index') }}">
                            <span class="s-icon"><i class="fa fa-list" style="color: #25D366;"></i></span>
                            <span class="s-text">Groupes Autorisés</span>
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('admin.settings.integrations') ? 'active' : '' }}">
                        <a href="{{ route('admin.settings.integrations') }}">
                            <span class="s-icon"><i class="ti-plug" style="color: #1877F2;"></i></span>
                            <span class="s-text">Intégrations & APIs</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.settings.apks') }}">
                            <span class="s-icon"><i class="ti-android" style="color: #3DDC84;"></i></span>
                            <span class="s-text">Mise à jour APKs</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="with-sub">
                <a href="#" class="waves-effect waves-light">
                    <span class="s-caret"><i class="fa fa-angle-down"></i></span>
                    <span class="s-icon"><i class="ti-announcement"></i></span>
                    <span class="s-text">Actualités (News)</span>
                </a>
                <ul>
                    <li><a href="{{ route('admin.news.index') }}">Toutes les actualités</a></li>
                    <li><a href="{{ route('admin.news.create') }}">Publier une actualité</a></li>
                </ul>
            </li>

            <!-- PARTAGE & ITINÉRAIRES -->
            <li class="menu-title">@lang('admin.include.service_sharing')</li>
            <li class="with-sub">
                <a href="#" class="waves-effect waves-light">
                    <span class="s-caret"><i class="fa fa-angle-down"></i></span>
                    <span class="s-icon"><i class="ti-direction"></i></span>
                    <span class="s-text">@lang('admin.include.transit_routes')</span>
                </a>
                <ul>
                    <li><a href="{{ route('admin.interurban-company.index') }}">Compagnies Interurbaines</a></li>
                    <li><a href="{{ route('admin.pdp-route.index') }}">Liste des itinéraires</a></li>
                    <li><a href="{{ route('admin.pdp-route.create') }}">Ajouter un itinéraire</a></li>
                    <li><a href="{{ route('admin.pdp-stop.index') }}">Liste des arrêts</a></li>
                    <li><a href="{{ route('admin.pdp-stop.create') }}">Ajouter un arrêt</a></li>
                    <li><a href="{{ route('admin.pdp-route-segment.index') }}">Liste des segments</a></li>
                    <li><a href="{{ route('admin.pdp-route-segment.create') }}">Ajouter un segment</a></li>
                </ul>
            </li>

            <!-- MEMBRES -->
            <li class="menu-title">@lang('admin.include.members')</li>
            <li class="with-sub">
                <a href="#" class="waves-effect waves-light">
                    <span class="s-caret"><i class="fa fa-angle-down"></i></span>
                    <span class="s-icon"><i class="ti-crown"></i></span>
                    <span class="s-text">@lang('admin.include.users')</span>
                </a>
                <ul>
                    <li><a href="{{ route('admin.user.index') }}">@lang('admin.include.list_users')</a></li>
                    <li><a href="{{ route('admin.user.create') }}">@lang('admin.include.add_new_user')</a></li>
                    <li><a href="{{ route('admin.user-subscription-plans.index') }}">Abonnements Utilisateurs</a></li>
                    <li><a href="{{ route('admin.user.kyc') }}">Vérification KYC</a></li>
                </ul>
            </li>
            <li class="with-sub">
                <a href="#" class="waves-effect waves-light">
                    <span class="s-caret"><i class="fa fa-angle-down"></i></span>
                    <span class="s-icon"><i class="ti-car"></i></span>
                    <span class="s-text">@lang('admin.include.providers')</span>
                </a>
                <ul>
                    <li><a href="{{ route('admin.provider.index') }}">@lang('admin.include.list_providers')</a></li>
                    <li><a href="{{ route('admin.provider.create') }}">@lang('admin.include.add_new_provider')</a></li>
                    <li><a href="{{ route('admin.subscription.index') }}">Subscriptions</a></li>
                </ul>
            </li>
            <li class="with-sub">
                <a href="#" class="waves-effect waves-light">
                    <span class="s-caret"><i class="fa fa-angle-down"></i></span>
                    <span class="s-icon"><i class="ti-crown"></i></span>
                    <span class="s-text">@lang('admin.include.dispatcher')</span>
                </a>
                <ul>
                    <li><a href="{{ route('admin.dispatch-manager.index') }}">@lang('admin.include.list_dispatcher')</a>
                    </li>
                    <li><a
                            href="{{ route('admin.dispatch-manager.create') }}">@lang('admin.include.add_new_dispatcher')</a>
                    </li>
                </ul>
            </li>
            <li class="with-sub">
                <a href="#" class="waves-effect waves-light">
                    <span class="s-caret"><i class="fa fa-angle-down"></i></span>
                    <span class="s-icon"><i class="ti-crown"></i></span>
                    <span class="s-text">Partenaires (Unified)</span>
                </a>
                <ul>
                    <li><a href="{{ route('admin.partner.index') }}">Liste des Partenaires</a></li>
                    <li><a href="{{ route('admin.partner.create') }}">Ajouter un Partenaire</a></li>
                </ul>
            </li>
            <li class="with-sub">
                <a href="#" class="waves-effect waves-light">
                    <span class="s-caret"><i class="fa fa-angle-down"></i></span>
                    <span class="s-icon"><i class="ti-crown"></i></span>
                    <span class="s-text">@lang('admin.include.account_manager')</span>
                </a>
                <ul>
                    <li><a
                            href="{{ route('admin.account-manager.index') }}">@lang('admin.include.list_account_managers')</a>
                    </li>
                    <li><a
                            href="{{ route('admin.account-manager.create') }}">@lang('admin.include.add_new_account_manager')</a>
                    </li>
                </ul>
            </li>

            <!-- COURSES & DÉTAILS -->
            <li class="menu-title">@lang('admin.include.requests')</li>
            <li>
                <a href="{{ route('admin.requests.index') }}" class="waves-effect waves-light">
                    <span class="s-icon"><i class="ti-infinite"></i></span>
                    <span class="s-text">@lang('admin.include.request_history')</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.requests.scheduled') }}" class="waves-effect waves-light">
                    <span class="s-icon"><i class="ti-palette"></i></span>
                    <span class="s-text">@lang('admin.include.scheduled_rides')</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.map.index') }}" class="waves-effect waves-light">
                    <span class="s-icon"><i class="ti-map-alt"></i></span>
                    <span class="s-text">@lang('admin.include.map')</span>
                </a>
            </li>
            <li class="with-sub">
                <a href="#" class="waves-effect waves-light">
                    <span class="s-caret"><i class="fa fa-angle-down"></i></span>
                    <span class="s-icon"><i class="ti-view-grid"></i></span>
                    <span class="s-text">@lang('admin.include.ratings') & @lang('admin.include.reviews')</span>
                </a>
                <ul>
                    <li><a href="{{ route('admin.user.review') }}">@lang('admin.include.user_ratings')</a></li>
                    <li><a href="{{ route('admin.provider.review') }}">@lang('admin.include.provider_ratings')</a></li>
                </ul>
            </li>
            <li>
                <a href="{{ route('admin.insurance.dashboard') }}" class="waves-effect waves-light">
                    <span class="s-icon"><i class="ti-shield"></i></span>
                    <span class="s-text">Mutuelle Assurance</span>
                </a>
            </li>

            <!-- CONFIGURATION GÉNÉRALE -->
            <li class="menu-title">@lang('admin.include.general')</li>
            <li class="with-sub">
                <a href="#" class="waves-effect waves-light">
                    <span class="s-caret"><i class="fa fa-angle-down"></i></span>
                    <span class="s-icon"><i class="ti-layout-tab"></i></span>
                    <span class="s-text">@lang('admin.include.documents')</span>
                </a>
                <ul>
                    <li><a href="{{ route('admin.document.index') }}">@lang('admin.include.list_documents')</a></li>
                    <li><a href="{{ route('admin.document.create') }}">@lang('admin.include.add_new_document')</a></li>
                </ul>
            </li>
            <li class="with-sub">
                <a href="#" class="waves-effect waves-light">
                    <span class="s-caret"><i class="fa fa-angle-down"></i></span>
                    <span class="s-icon"><i class="ti-layout-tab"></i></span>
                    <span class="s-text">@lang('admin.include.promocodes')</span>
                </a>
                <ul>
                    <li><a href="{{ route('admin.promocode.index') }}">@lang('admin.include.list_promocodes')</a></li>
                    <li><a href="{{ route('admin.promocode.create') }}">@lang('admin.include.add_new_promocode')</a>
                    </li>
                </ul>
            </li>
            <li class="with-sub">
                <a href="#" class="waves-effect waves-light">
                    <span class="s-caret"><i class="fa fa-angle-down"></i></span>
                    <span class="s-icon"><i class="ti-layout-tab"></i></span>
                    <span class="s-text">@lang('admin.include.hospitals')</span>
                </a>
                <ul>
                    <li><a href="{{ route('admin.hospital.index') }}">@lang('admin.include.hospital_list')</a></li>
                    <li><a href="{{ route('admin.hospital.create') }}">@lang('admin.include.add_hospital')</a></li>
                </ul>
            </li>

            <!-- PUBLICITÉ & MARKETING -->
            <li class="menu-title">Publicité & Marketing</li>
            <li class="with-sub">
                <a href="#" class="waves-effect waves-light">
                    <span class="s-caret"><i class="fa fa-angle-down"></i></span>
                    <span class="s-icon"><i class="ti-bullhorn"></i></span>
                    <span class="s-text">Campagnes Publicitaires</span>
                </a>
                <ul>
                    <li><a href="{{ route('admin.ad-campaign.index') }}">Liste des campagnes</a></li>
                    <li><a href="{{ route('admin.ad-campaign.create') }}">Créer une campagne</a></li>
                </ul>
            </li>

            <!-- PARAMÈTRES -->
            <li class="menu-title">@lang('admin.include.settings')</li>
            <li>
                <a href="{{ route('admin.settings') }}" class="waves-effect waves-light">
                    <span class="s-icon"><i class="ti-settings"></i></span>
                    <span class="s-text">@lang('admin.include.site_settings')</span>
                </a>
            </li>

            <!-- AUTRES -->
            <li class="menu-title">@lang('admin.include.others')</li>
            <li>
                <a href="{{ route('admin.privacy') }}" class="waves-effect waves-light">
                    <span class="s-icon"><i class="ti-help"></i></span>
                    <span class="s-text">@lang('admin.include.privacy_policy')</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.support.chat') }}" class="waves-effect waves-light">
                    <span class="s-icon" style="color: #4CAF50;"><i class="ti-comment-alt"></i></span>
                    <span class="s-text">Chat Support (IA)</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.help') }}" class="waves-effect waves-light">
                    <span class="s-icon"><i class="ti-help"></i></span>
                    <span class="s-text">@lang('admin.include.help')</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.push') }}" class="waves-effect waves-light">
                    <span class="s-icon"><i class="ti-smallcap"></i></span>
                    <span class="s-text">@lang('admin.include.custom_push')</span>
                </a>
            </li>
            <li>
                <a href="{{route('admin.translation') }}" class="waves-effect waves-light">
                    <span class="s-icon"><i class="ti-smallcap"></i></span>
                    <span class="s-text">@lang('admin.include.translations')</span>
                </a>
            </li>

            <!-- COMPTE -->
            <li class="menu-title">@lang('admin.include.account')</li>
            <li>
                <a href="{{ route('admin.profile') }}" class="waves-effect waves-light">
                    <span class="s-icon"><i class="ti-user"></i></span>
                    <span class="s-text">@lang('admin.include.account_settings')</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.password') }}" class="waves-effect waves-light">
                    <span class="s-icon"><i class="ti-exchange-vertical"></i></span>
                    <span class="s-text">@lang('admin.include.change_password')</span>
                </a>
            </li>
            <li class="compact-hide">
                <a href="{{ url('/admin/logout') }}" onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();">
                    <span class="s-icon"><i class="ti-power-off"></i></span>
                    <span class="s-text">@lang('admin.include.logout')</span>
                </a>
                <form id="logout-form" action="{{ url('/admin/logout') }}" method="POST" style="display: none;">
                    {{ csrf_field() }}
                </form>
            </li>
        </ul>
    </div>
</div>