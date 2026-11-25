// CONTACT FORM - PHP

const contactForm =
    document.getElementById("contactForm");

const formMessage =
    document.getElementById("formMessage");


if (contactForm) {

    contactForm.addEventListener(
        "submit",
        async function (event) {

            event.preventDefault();


            const submitButton =
                contactForm.querySelector(
                    ".submit-btn"
                );


            const formData =
                new FormData(contactForm);


            submitButton.disabled = true;

            submitButton.querySelector("span")
                .textContent = "Sending...";


            try {

                const response =
                    await fetch(
                        "contact.php",
                        {
                            method: "POST",
                            body: formData
                        }
                    );


                const result =
                    await response.text();


                if (result.trim() === "success") {

                    formMessage.textContent =
                        "Message sent successfully! I'll get back to you soon.";

                    formMessage.classList.add(
                        "success"
                    );

                    contactForm.reset();

                } else {

                    formMessage.textContent =
                        result;

                    formMessage.classList.add(
                        "error"
                    );

                }


            } catch (error) {

                formMessage.textContent =
                    "Something went wrong. Please try again.";

                formMessage.classList.add(
                    "error"
                );

            }


            submitButton.disabled = false;

            submitButton.querySelector("span")
                .textContent = "Send Message";

        }
    );

}