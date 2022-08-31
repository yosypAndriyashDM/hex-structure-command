%%php_open_tag%%

namespace %%namespace%%;

%%use_definitions%%

class %%class_name%%
{
    public function __construct(
%%domain_service_class_construct_args%%
    )
    {
    }

    public function execute(
%%domain_service_execute_args%%
    ): %%domain_service_execute_response_type%%
    {
%%domain_service_execute_body%%
    }
}
