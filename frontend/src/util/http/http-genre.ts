import HttpResource from "./http-resource";
import {httpVideo} from "./index";

const httpGenre = new HttpResource(httpVideo, "genres");

export default httpGenre;