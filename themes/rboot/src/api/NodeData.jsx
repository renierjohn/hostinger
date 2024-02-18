import { ComponentBaseData } from '../redux/store';

const URL = `http://renifysite.local/jsonapi/node/landing_pages/31bde1ab-780f-4a39-a69e-ca33f0e27ad7?resourceVersion=id%3A822`;
const data =
  {
    'id': 1,
    'type': `node`,
    'key': `main`,
    'title': `Landing Page`,
    'body': `Lorem ipsum subtitle`,
    'component_banner': {
      'id': 31,
      'type': `banner_cta`
    },
    'component_body':
      [
        // {
        //   'id': 3,
        //   'machine_name': 'group',
        //   'type': 'paragraph'
        // },
        {
          'id': 3,
          'machine_name': 'card_group',
          'type': 'paragraph'
        },
        {
          'id': 12,
          'machine_name': 'group',
          'type': 'paragraph'
        }
        // {
        //   'id': 16,
        //   'machine_name': 'card_group',
        //   'type': 'paragraph'
        // },
      ]
  }

export const NodeData = ComponentBaseData(data);

export default {
    NodeData,
}
