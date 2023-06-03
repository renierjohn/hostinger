import { ComponentBaseData } from '../redux/store';

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
        {
          'id': 2,
          'key': 'group',
          'type': 'paragraph'
        },
        {
          'id': 13,
          'key': 'card_group',
          'type': 'paragraph'
        }
      ]
  }

export const NodeData = ComponentBaseData(data);

export default {
    NodeData,
}
