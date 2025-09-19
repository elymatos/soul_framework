<?php

namespace App\View\_Components\Combobox;

use App\Repositories\Lemma as LemmaRepository;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Lemma extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $id,
        public string $label,
        public string $placeholder = '',
        public string $name = '',
        public ?int $value = 0,
    ) {
        debug('value', $this->value);
        if (($this->value != '') && ($this->value != 0)) {
            $lemma = LemmaRepository::byId($this->value);
            $this->placeholder = $lemma->name;
        } else {
            $this->placeholder = 'Search Lemma';
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.combobox.lemma');
    }
}
