import HttpResource from "./http-resource";
import {httpVideo} from "./index";

const httpCastMember = new HttpResource(httpVideo, "cast_members");

export default httpCastMember;