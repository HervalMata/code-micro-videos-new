import HttpResource from "./http-resource";
import {httpVideo} from "./index";

const httpCategory = new HttpResource(httpVideo, "categories");

export default httpCategory;