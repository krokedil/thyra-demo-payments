const { registerPaymentMethod } = window.wc.wcBlocksRegistry;
const { getSetting } = window.wc.wcSettings;
const { createElement } = window.wp.element;

//Lade till supports för att kunna använda produkterna i checkouten
const settings = getSetting("kdp_gateway_data", {}); //Hämtar inställningarna för betalningsmetoden från WooCommerce Blocks
const title = settings.title || "Krokedil Demo Payments";
const description = settings.description || "";
const Content = () => createElement("p", null, description); //Skapar en Content komponent som visar beskrivningen av betalningsmetoden

//registrerar betalningsmetoden för WooCommerce Blocks
registerPaymentMethod({
  name: "kdp_gateway",
  label: title,
  ariaLabel: title,
  content: createElement(Content),
  edit: createElement(Content),
  canMakePayment: () => true,
  //Claude: lade till supports för att kunna använda produkterna i checkouten
  supports: {
    features: ["products"],
  },
});
