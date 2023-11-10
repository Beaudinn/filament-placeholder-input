<?php

namespace Codedor\FilamentPlaceholderInput\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Concerns;
use Filament\Forms\Components\Contracts\HasHintActions;
use Filament\Forms\Components\Field;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;

class PlaceholderInput extends Component implements HasHintActions
{
	use Concerns\HasHelperText;
	use Concerns\HasHint;
	use Concerns\HasName;

    protected string $view = 'filament-placeholder-input::forms.components.placeholder-input';

    public null|array|string|Closure $linksWith = null;

    public null|array|Closure $variables = null;

    public bool|Closure $canCopy = false;

	public static function make(string $name): static
	{
		$static = app(static::class, ['name' => $name]);
		$static->configure();

		return $static;
	}


	public function linksWith(null|array|string|Closure $linksWith): static
    {
        if (is_string($linksWith)) {
            $linksWith = Arr::wrap($linksWith);
        }

        $this->linksWith = $linksWith;

        return $this;
    }

    public function getLinksWith(): Collection
    {
        $form = $this->getLivewire()->getForm('form');
		$activeLocale = $this->getLivewire()->activeLocale;
        return collect($this->evaluate($this->linksWith))->mapWithKeys(fn ($key) => [
            $key => $form->getComponent("data.{$activeLocale}.{$key}")->getLabel(),
        ]);
    }

	public function getActiveLocale()
	{
		return $this->getLivewire()->activeLocale;
	}

    public function variables(array|Closure $variables): static
    {
        $this->variables = $variables;

        return $this;
    }

    public function getVariables(): ?array
    {
        return $this->evaluate($this->variables) ?? method_exists($this->getRecord(), 'getPlaceholderVariables')
            ? $this->getRecord()->getPlaceholderVariables()
            : [];
    }

    public function copyable(bool|Closure $canCopy = true): static
    {
        $this->canCopy = $canCopy;

        return $this;
    }

    public function canCopy(): bool
    {
        return Request::secure() && $this->evaluate($this->canCopy);
    }
}
