%%php_open_tag%%

namespace %%namespace%%;

%%use_definitions%%

class %%class_name%% implements ApplicationServiceResponse
{

    public function __construct(
%%application_read_response_constructor_args%%
    )
    {
    }

%%application_read_response_getters_body%%

}