<?php declare(strict_types=1);

namespace GuardsmanPanda\Larabear\Web\Www\Shared\Component\Dialog;

use GuardsmanPanda\Larabear\Infrastructure\Http\Service\Resp;
use Illuminate\View\Component;
use Illuminate\View\View;

final class Update  extends Component {
    public function __construct(
        public readonly string $title = 'Update',
        public readonly string $submitText = 'Update',
        public readonly string $cancelText = 'Cancel',
    ) {}

    public function render(): View {
        return Resp::view(view: 'bear::dialog.crud');
    }
}