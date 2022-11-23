<?php

namespace GuardsmanPanda\Larabear\Web\Www\Auth\Controller;

use GuardsmanPanda\Larabear\Infrastructure\Auth\Crud\BearUserUpdater;
use GuardsmanPanda\Larabear\Infrastructure\Config\Service\BearConfigService;
use GuardsmanPanda\Larabear\Infrastructure\Http\Service\Req;
use GuardsmanPanda\Larabear\Infrastructure\Http\Service\Resp;
use GuardsmanPanda\Larabear\Infrastructure\Oauth2\Service\BearOauth2ClientService;
use GuardsmanPanda\Larabear\Infrastructure\Oauth2\Model\BearOauth2Client;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Throwable;

class LarabearAuthController extends Controller {
    public function showSignInForm(): View {
        return view(view: 'Auth::sign-in-form', data: [
            'oauth2_clients' => DB::select(query: 'SELECT oauth2_client_id, oauth2_client_type FROM bear_oauth2_client'),
        ]);
    }

    public function oauth2Redirect(string $oauth2_client_id): RedirectResponse {
        return BearOauth2ClientService::getAuthorizeRedirectResponse(
            client: BearOauth2Client::findOrFail(id: $oauth2_client_id),
            afterSignInRedirectPath: Req::getString(key: 'redirect-path', nullIfMissing: true),
            loginUser: Req::getBoolOrDefault(key: 'login-user', default: true),
            overwriteRedirectUri: "/bear/auth/oauth2-client/$oauth2_client_id/callback",
            specialScope: Req::getString(key: 'special-scope', nullIfMissing: true),
            accountPrompt: Req::getBoolOrDefault(key: 'account-prompt', default: false),
        );
    }

    public static function oauth2Callback(string $oauth2_client_id): RedirectResponse {
        try {
            if (Req::getStringOrDefault(key: 'state', default: '-----') !== Session::get(key: 'oauth2_state')) {
                return Resp::redirectWithMessage(url: BearConfigService::getString(config_key: 'larabear-auth.path_to_redirect_if_not_logged_in'), message: 'Invalid state');
            }
            $redirectUri = config(key: 'app.url') . "/bear/auth/oauth2-client/$oauth2_client_id/callback";
            $createUserIfNotExists = BearConfigService::getBoolean(config_key: 'larabear-auth.oauth2_create_user_if_not_exists');
            if (Session::get(key: 'oauth2_login_user', default: false) !== true) {
                $createUserIfNotExists = false;
            }
            $user = BearOauth2ClientService::getUserFromCallback(
                client: BearOauth2Client::findOrFail(id: $oauth2_client_id),
                code: Req::getStringOrDefault(key: 'code'),
                redirectUri: $redirectUri, createBearUser: $createUserIfNotExists
            );
            if (Session::get(key: 'oauth2_login_user', default: false) === true) {
                BearUserUpdater::fromId(id: $user->id)->setLastLoginNow()->save();
                Session::put(key: 'bear_user_id', value: $user->user_id);
            }
        } catch (Throwable $t) {
            return Resp::redirectWithMessage(url: BearConfigService::getString(config_key: 'larabear-auth.path_to_redirect_if_not_logged_in'), message: $t->getMessage());
        }
        return new RedirectResponse(url: Session::get(key: 'oauth2_redirect_url') ?? BearConfigService::getString(config_key: 'larabear-auth.path_to_redirect_after_login'));
    }
}