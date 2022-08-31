%%php_open_tag%%

namespace %%namespace%%;

%%use_definitions%%

class %%class_name%% implements ApplicationServiceResponse
{
    public function __construct(
%%application_command_response_construct_class_parameters%%
    )
    {
    }

%%application_command_response_parameters_getters%%
}