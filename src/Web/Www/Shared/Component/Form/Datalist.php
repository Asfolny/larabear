<?php declare(strict_types=1);

namespace GuardsmanPanda\Larabear\Web\Www\Shared\Component\Form;

use GuardsmanPanda\Larabear\Infrastructure\Http\Service\Resp;
use Illuminate\View\View;
use Illuminate\View\Component;

final class Datalist extends Component {
    /**
     * @param string $id
     * @param string $label
     * @param string|null $hxTrigger
     * @param array<string, string> $options
     */
    public function __construct(
        public readonly string $id,
        public string $label = '',
        public readonly string|null $hxTrigger = null,
        public readonly array $options = [],
    ) {
        if ($this->label === '') {
            $this->label = ucwords(string: str_replace(search: '_', replace: ' ', subject: $this->id));
        }
    }

    public function render(): View {
        return Resp::view(view: 'bear::form.datalist');
    }
}
