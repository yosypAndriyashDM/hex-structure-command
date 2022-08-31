%%php_open_tag%%

namespace %%namespace%%;

class %%class_name%%
{
    public function __construct(
%%domain_class_construct_params%%
    )
    {
    }

    public static function create(
%%domain_class_create_params%%
    ): self
    {
        return new static(
%%domain_class_create_arguments%%
        );
    }

%%domain_class_getters%%
}