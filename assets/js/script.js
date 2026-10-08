document.addEventListener(
    "DOMContentLoaded",
    function () {


        // =================================
        // AUTO HIDE ALERT
        // =================================

        const alerts =
            document.querySelectorAll(
                ".alert"
            );


        alerts.forEach(function (alert) {

            setTimeout(function () {

                alert.style.transition =
                    "opacity .5s";

                alert.style.opacity = "0";


                setTimeout(function () {

                    alert.remove();

                }, 500);

            }, 5000);

        });


        // =================================
        // PHONE VALIDATION
        // =================================

        const phoneInputs =
            document.querySelectorAll(
                'input[name="phone"]'
            );


        phoneInputs.forEach(function (input) {

            input.addEventListener(
                "input",
                function () {

                    this.value =
                        this.value.replace(
                            /[^0-9]/g,
                            ""
                        );

                }
            );

        });


        // =================================
        // CONFIRM DELETE
        // =================================

        const deleteButtons =
            document.querySelectorAll(
                ".delete-btn"
            );


        deleteButtons.forEach(function (button) {

            button.addEventListener(
                "click",
                function (event) {

                    if (
                        !confirm(
                            "Are you sure you want to delete this record?"
                        )
                    ) {

                        event.preventDefault();

                    }

                }
            );

        });

    }
);