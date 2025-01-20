<?php

namespace App\Livewire\HumanResources\HRDept;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\HumanResources\ConditionalIncentiveCategoryItem;

class CICategoryItem extends Component
{
	public $cicategory;


	#[On('cicategoryitemcreate')]
	#[On('cicategorycreate')]
	public function render()
	{
		return view('livewire.humanresources.hrdept.cicategoryitem', [
			'cicategory' => $this->cicategory
		]);
	}

	public function deltem($id)
	{
		ConditionalIncentiveCategoryItem::find($id)->delete();
		$this->dispatch('cicategoryitemdel');
	}

}
