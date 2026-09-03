<?php

namespace Woodynew\DcatAdminKit\Support;

use Dcat\Admin\Form;

class AdminFormUtil
{
    public static function isCreatingEditing(Form $form, $existField = []): bool
    {
        if ($form->isCreating()) {
            return true;
        }

        if (! $form->isEditing()) {
            return false;
        }

        if (request('_file_del_') || request('_file_')) {
            return false;
        }

        foreach ((array) $existField as $field) {
            if (! request()->has($field)) {
                return false;
            }
        }

        return true;
    }
}
