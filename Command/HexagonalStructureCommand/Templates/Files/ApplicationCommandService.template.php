%%php_open_tag%%

namespace %%namespace%%;

%%use_definitions%%

class %%class_name%%
{
%%application_command_service_domain_service_instance%%

%%application_command_service_class_constructor%%

    public function execute(
%%application_command_service_command_class_type%% $command
    ): ApplicationServiceResponse
    {
%%application_command_service_execute_prepare_params_from_query%%
%%application_command_service_domain_call_stub%%

        return new %%application_command_service_response_class_type%%(
%%application_command_service_response_domain_args%%
        );
    }
}
